<?php

/*
 * This file is part of the Access to Memory (AtoM) software.
 *
 * Access to Memory (AtoM) is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Access to Memory (AtoM) is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Access to Memory (AtoM).  If not, see <http://www.gnu.org/licenses/>.
 */

namespace arxiu;
use arBaseTask;
use QubitActor;
use sfCommandOption;

/**
 * Find authority records duplicates.
 *
 * @author     Orestes Sanchez <orestes@estotienearreglo.es>
 */
class arArxiuPluginFindDuplicateTask extends arBaseTask
{
    protected function configure()
    {
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
                'The name of the culture to use for duplicates (defaults to "en")',
                'en'
            ),
            new sfCommandOption(
                'dry-run',
                'd',
                sfCommandOption::PARAMETER_NONE,
                'Dry run (no database changes)',
                null),
        ]);

        $this->namespace = 'arxiu';
        $this->name = 'duplicates';
        $this->briefDescription = 'Find authority records duplicates';
        $this->detailedDescription = <<<'EOF'
Find authority records duplicates. Names are cleaned of prefixes and suffixes and normalized for comparison. 
The script produces a report of authority records that have the same name. 
It chooses an arbitrary name to be the canonical name. It is up to the user to choose which one to keep and which ones to delete.
EOF;
    }

    protected function execute($arguments = [], $options = [])
    {
        parent::execute($arguments, $options);

        // Remind the user they are in dry run mode
        if ($options['dry-run']) {
            $this->log('*** DRY RUN (no changes will be made to the database) ***');
        }

        $this->log("Normalizing for '".$options['culture']."' culture...");
        // Get all authority records
        $actors = QubitActor::getAllNames();
        $actors = QubitActor::getAll();

        $this->log('Found '.count($actors).' authority records');
        // Log names
        $this->log(json_encode($actors, JSON_PRETTY_PRINT));
    }
}
