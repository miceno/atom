<?php

use PHPUnit\Framework\TestCase;

require_once dirname(__FILE__).'/../../lib/task/taxonomy/taxonomyNormalizeTask.class.php';

// Inline mocks for missing dependencies
if (!class_exists('QubitPdo')) {
    class QubitPdo {
        public static function fetchAll($sql, $params, $opts) {
            if (strpos($sql, 'SELECT t.id, i.name FROM term t') !== false) {
                return isset($GLOBALS['mock_terms']) ? $GLOBALS['mock_terms'] : [];
            }
            return [];
        }
        public static function modify($sql, $params) {
            $GLOBALS['pdo_modify'][] = [$sql, $params];
        }
    }
}
if (!class_exists('QubitSearch')) {
    class QubitSearch {
        public static function getInstance() { return new self(); }
        public function update($o) { $GLOBALS['search_updated'][] = $o->id; }
    }
}
if (!class_exists('QubitTaxonomy')) {
    class QubitTaxonomy {
        const LEVEL_OF_DESCRIPTION_ID = 99;
    }
}
if (!class_exists('sfException')) {
    class sfException extends Exception {}
}
if (!class_exists('QubitFlatfileImport')) {
    class QubitFlatfileImport {
        public static function sqlQuery($sql, $params) {
            return new class($sql, $params) {
                private $sql;
                private $params;
                private $fetched = false;
                public function __construct($sql, $params) {
                    $this->sql = $sql;
                    $this->params = $params;
                }
                public function fetch($mode) {
                    // Taxonomy query
                    if (strpos($this->sql, 'SELECT id FROM taxonomy_i18n') !== false) {
                        if (isset($GLOBALS['mock_taxonomy']) && $GLOBALS['mock_taxonomy'] !== null && !$this->fetched) {
                            $this->fetched = true;
                            return $GLOBALS['mock_taxonomy'];
                        }
                        return false;
                    }
                    return false;
                }
            };
        }
    }
}
if (!class_exists('QubitTerm')) {
    class QubitTerm {
        public static function getById($id) {
            $obj = new self();
            $obj->id = $id;
            $obj->deleted = false;
            $obj->delete = function() use ($obj) { $obj->deleted = true; };
            return $obj;
        }
    }
}

class taxonomyNormalizeTaskTestProxy extends taxonomyNormalizeTask {
    protected $taxonomyId;

    public function getTaxonomyIdByName($name, $culture) {
        return parent::getTaxonomyIdByName($name, $culture);
    }
    public function populateTaxonomyNameUsage(&$names, $culture): void {
        parent::populateTaxonomyNameUsage($names, $culture);
    }
    public function normalizeTaxonomy($names, &$affectedObjects, $dry_run = false): void {
        parent::normalizeTaxonomy($names, $affectedObjects, $dry_run);
    }
    public function setTaxonomyId($id): void {
        $this->taxonomyId = $id;
    }
}

class taxonomyNormalizeTaskTest extends TestCase
{
    protected $task;
    protected $dispatcher = null;
    protected $formatter = null;

    protected function setUp(): void
    {
        require_once dirname(__FILE__).'/../../vendor/symfony/lib/event_dispatcher/sfEventDispatcher.php';
        require_once dirname(__FILE__).'/../../vendor/symfony/lib/command/sfFormatter.class.php';
        $this->dispatcher = new sfEventDispatcher();
        $this->formatter = new sfFormatter();
        $this->task = new taxonomyNormalizeTaskTestProxy($this->dispatcher, $this->formatter);
        $GLOBALS['mock_terms'] = [];
        $GLOBALS['mock_taxonomy'] = null;
        $GLOBALS['pdo_modify'] = [];
        $GLOBALS['search_updated'] = [];
    }

    public function testGetTaxonomyIdByNameSuccess()
    {
        $mock = new stdClass();
        $mock->id = 42;
        $GLOBALS['mock_taxonomy'] = $mock;
        $id = $this->task->getTaxonomyIdByName('TestTax', 'en');
        $this->assertEquals(42, $id, 'getTaxonomyIdByName returns correct ID');
    }

    public function testGetTaxonomyIdByNameFailure()
    {
        $GLOBALS['mock_taxonomy'] = null;
        $id = $this->task->getTaxonomyIdByName('MissingTax', 'en');
        $this->assertFalse($id, 'getTaxonomyIdByName returns false for missing taxonomy');
    }

    public function testPopulateTaxonomyNameUsage()
    {
        $GLOBALS['mock_terms'] = [
            (object)['id' => 1, 'name' => 'A'],
            (object)['id' => 2, 'name' => 'A'],
            (object)['id' => 3, 'name' => 'B'],
        ];
        $this->task->setTaxonomyId(123); // Ensure taxonomyId is set
        $names = [];
        $this->task->populateTaxonomyNameUsage($names, 'en');
        $expectedKeys = ['A','B'];
        ksort($expectedKeys);
        $actualKeys = array_keys($names);
        ksort($actualKeys);
        $this->assertEquals($expectedKeys, $actualKeys, 'populateTaxonomyNameUsage populates names');
        $this->assertCount(2, $names['A'], 'populateTaxonomyNameUsage groups duplicate names');
    }

    public function testNormalizeTaxonomyDryRun()
    {
        $affected = [];
        $names = ['A' => [2, 1], 'B' => [3]];
        $this->task->normalizeTaxonomy($names, $affected, true);
        $this->assertEquals([], $affected, 'normalizeTaxonomy in dry-run does not affect objects');
    }
}
