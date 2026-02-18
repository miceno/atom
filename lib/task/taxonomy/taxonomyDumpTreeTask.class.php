<?php

/*
 * This file is part of the Access to Memory (AtoM) software.
 *
 * Access to Memory (AtoM) is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * AtoM is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Access to Memory (AtoM).  If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * Dump taxonomy tree.
 *
 * @author     Copilot
 */
class taxonomyDumpTreeTask extends arBaseTask
{
    protected function configure()
    {
        $this->addArguments([
            new sfCommandArgument('taxonomy-name', sfCommandArgument::REQUIRED, 'The name of the taxonomy to dump'),
        ]);

        $this->addOptions([
            new sfCommandOption(
                'application',
                null,
                sfCommandOption::PARAMETER_REQUIRED,
                'The application name',
                'qubit'
            ),
            new sfCommandOption(
                'env',
                null,
                sfCommandOption::PARAMETER_REQUIRED,
                'The environment',
                'cli'
            ),
            new sfCommandOption(
                'culture',
                null,
                sfCommandOption::PARAMETER_OPTIONAL,
                'The name of the culture to use (defaults to "en")',
                'en'
            ),
            new sfCommandOption(
                'parent-id',
                null,
                sfCommandOption::PARAMETER_OPTIONAL,
                'The parent term ID to start from (defaults to root terms)',
                null
            ),
        ]);

        $this->namespace = 'taxonomy';
        $this->name = 'dumptree';
        $this->briefDescription = 'Dump taxonomy tree';
        $this->detailedDescription = <<<'EOF'
Dump taxonomy tree
EOF;
    }

    protected function execute($arguments = [], $options = [])
    {
        parent::execute($arguments, $options);

        $taxonomyId = $this->getTaxonomyIdByName($arguments['taxonomy-name'], $options['culture']);
        if (!$taxonomyId) {
            throw new sfException("A taxonomy named '".$arguments['taxonomy-name']."' not found for culture '".$options['culture']."'.");
        }

        $parentId = $options['parent-id'] !== null ? $options['parent-id'] : null;
        $this->log("Dumping taxonomy tree for '".$options['culture']."' culture...");
        $this->dumpTree($taxonomyId, $options['culture'], $parentId);
    }

    protected function getTaxonomyIdByName($name, $culture)
    {
        $sql = "SELECT id FROM taxonomy_i18n WHERE culture=? AND name=?";
        $statement = QubitFlatfileImport::sqlQuery($sql, [$culture, $name]);
        if ($object = $statement->fetch(PDO::FETCH_OBJ)) {
            return $object->id;
        }
        return false;
    }

    protected function dumpTree($taxonomyId, $culture, $parentId = null, $level = 0)
    {
        $terms = $this->getTerms($taxonomyId, $culture, $parentId);
        foreach ($terms as $term) {
            $indent = str_repeat('  ', $level);
            $this->log($indent . $term->name . ' (ID: ' . $term->id . ')');
            $this->dumpTree($taxonomyId, $culture, $term->id, $level + 1);
        }
    }

    protected function getTerms($taxonomyId, $culture, $parentId = null)
    {
        $sql = 'SELECT t.id, i.name FROM term t
            INNER JOIN term_i18n i ON t.id=i.id
            WHERE t.taxonomy_id=:taxonomyId AND i.culture=:culture';
        $params = [':taxonomyId' => $taxonomyId, ':culture' => $culture];
        if ($parentId === null) {
            $sql .= ' AND t.parent_id IS NULL';
        } else {
            $sql .= ' AND t.parent_id=:parentId';
            $params[':parentId'] = $parentId;
        }
        $sql .= ' ORDER BY i.name';
        return QubitPdo::fetchAll($sql, $params, ['fetchMode' => PDO::FETCH_OBJ]);
    }
}

