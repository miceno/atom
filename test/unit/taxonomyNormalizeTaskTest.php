<?php

require_once dirname(__FILE__).'/../bootstrap/unit.php';
require_once dirname(__FILE__).'/../../lib/task/taxonomy/taxonomyNormalizeTask.class.php';

// Mock missing dependencies
if (!class_exists('QubitPdo')) {
    class QubitPdo {
        public static function fetchAll($sql, $params, $opts) {
            // Return mock terms for testing
            return isset($GLOBALS['mock_terms']) ? $GLOBALS['mock_terms'] : [];
        }
        public static function modify($sql, $params) {
            $GLOBALS['pdo_modify'][] = [$sql, $params];
        }
    }
}
if (!class_exists('QubitFlatfileImport')) {
    class QubitFlatfileImport {
        public static function sqlQuery($sql, $params) {
            // Return a mock PDOStatement
            return new class {
                public function fetch($mode) {
                    return isset($GLOBALS['mock_taxonomy']) ? (object)$GLOBALS['mock_taxonomy'] : false;
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
if (!class_exists('QubitSearch')) {
    class QubitSearch {
        public static function getInstance() { return new self(); }
        public function update($o) { $GLOBALS['search_updated'][] = $o->id; }
    }
}
if (!class_exists('QubitInformationObject')) {
    class QubitInformationObject {
        public $id;
        public static function getById($id) { $obj = new self(); $obj->id = $id; return $obj; }
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

$t = new lime_test(5, new lime_output_color());

// Test: getTaxonomyIdByName success
$task = new taxonomyNormalizeTask();
$GLOBALS['mock_taxonomy'] = ['id' => 42];
$id = $task->getTaxonomyIdByName('TestTax', 'en');
$t->is($id, 42, 'getTaxonomyIdByName returns correct ID');

// Test: getTaxonomyIdByName failure
unset($GLOBALS['mock_taxonomy']);
$id = $task->getTaxonomyIdByName('MissingTax', 'en');
$t->is($id, false, 'getTaxonomyIdByName returns false for missing taxonomy');

// Test: populateTaxonomyNameUsage
$GLOBALS['mock_terms'] = [
    (object)['id' => 1, 'name' => 'A'],
    (object)['id' => 2, 'name' => 'A'],
    (object)['id' => 3, 'name' => 'B'],
];
$names = [];
$task->taxonomyId = 123;
$task->populateTaxonomyNameUsage($names, 'en');
$t->is(array_keys($names), ['A','B'], 'populateTaxonomyNameUsage populates names');
$t->is(count($names['A']), 2, 'populateTaxonomyNameUsage groups duplicate names');

// Test: normalizeTaxonomy merges terms
$affected = [];
$names = ['A' => [2, 1], 'B' => [3]];
$task->normalizeTaxonomy($names, $affected, true);
$t->is($affected, [], 'normalizeTaxonomy in dry-run does not affect objects');

