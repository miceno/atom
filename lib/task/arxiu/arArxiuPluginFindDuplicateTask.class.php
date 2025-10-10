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
            new sfCommandOption(
                'verbose',
                null,
                sfCommandOption::PARAMETER_OPTIONAL,
                'Verbose output',
                false),
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

        // Inform the user if dry-run mode is enabled
        if ($options['dry-run']) {
            $this->log('*** DRY RUN (no changes will be made to the database) ***');
        }

        // Log the culture being used for normalization
        $this->log("Normalizing for '" . $options['culture'] . "' culture...");

        // Retrieve all authority records
        $result_actors = QubitActor::getAllNames();

        // Normalize names for duplicate detection
        foreach ($result_actors as $key => $actor) {
            // Normalize the actor's name using the dedicated method
            if ($actor['nameId'] === null) {
                $result_actors[$key]['norm_name'] = $this->normalizeName($actor['name']);
            } else {
                // Skip parallel forms of name
                $this->log('Skipping parallel form of name: ' . $actor['name']);
            }
        }

        // Remove entries with non null nameId
        $result_actors = array_filter($result_actors, function ($actor) {
            return $actor['nameId'] === null;
        });

        // Log the total number of authority records found
        $this->log('Found ' . count($result_actors) . ' authority records');
        // $this->log(json_encode($result_actors, JSON_PRETTY_PRINT));

        // Log normalized names for review (avoid logging full actor data if sensitive)
        $this->log('Normalized names for review:');
        foreach ($result_actors as $actor) {
            $matches = $this->matchAuthor($actor, $result_actors);
            if ($options['verbose']) {
                if (count($matches) > 0) {
                    $this->log($actor['name'] . ' => ' . $actor['norm_name']);
                    $this->log('Matches: ' . count($matches) . ' (' . implode(', ', array_column($matches, 'name')) . ')');
                }
            }
        }
    }

    // Normalize a name for duplicate detection
    protected function normalizeName($name)
    {
        // Remove parentheses from the name
        $norm_name = preg_replace('/\([^)]*\)/', '', $name);
        // Normalize accents in UTF-8 using intl if available, otherwise fallback to iconv
        if (function_exists('transliterator_transliterate')) {
            // Use intl transliterator for better international support
            $norm_name = transliterator_transliterate('Any-Latin; Latin-ASCII; [\u0080-\u7fff] remove', $norm_name);
        } else {
            // Fallback to iconv
            $norm_name = iconv('UTF-8', 'ASCII//TRANSLIT', $norm_name);
        }
        // Convert to lowercase
        $norm_name = strtolower($norm_name);
        // Remove all non-alphanumeric except comma
        $norm_name = preg_replace('/[^a-zA-Z0-9, ]/', '', $norm_name);
        // Replace multiple spaces with a single space
        $norm_name = preg_replace('/\s+/', ' ', $norm_name);
        // Trim spaces
        $norm_name = trim($norm_name);

        return $norm_name;
    }

    // Match a source author object to a list of author objects according to the spec
    protected function matchAuthor($sourceAuthor, $authorList)
    {
        // Use the normalized name from the source author object
        $normSource = $sourceAuthor['norm_name'];

        // Initialize an array to hold matches
        $matches = [];

        // Iterate over each candidate author
        foreach ($authorList as $author) {
            // Skip authors with the same actorId as the source author
            if ($author['actorId'] === $sourceAuthor['actorId']) {
                continue;
            }
            // Check if the source author's normalized name contains a comma
            if (strpos($normSource, ',') !== false) {
                // Split the normalized name into last names and first name
                $parts = array_map('trim', explode(',', $normSource));
                // If there are two parts, handle "Last [Second], First"
                if (count($parts) === 2) {
                    // The full last name (may include second last name)
                    $lastNames = $parts[0];
                    // The first name
                    $firstName = $parts[1];
                    // Use the normalized name from the author object
                    $normAuthor = $author['norm_name'];
                    // Split the author in the list
                    $authorParts = array_map('trim', explode(',', $normAuthor));
                    if (count($authorParts) === 2) {
                        // Match first name and full last name
                        if ($authorParts[0] === $lastNames && $authorParts[1] === $firstName) {
                            $matches[] = $author;
                            continue;
                        }
                        // Match first name and only first last name
                        $firstLastName = explode(' ', $lastNames)[0];
                        $authorFirstLastName = explode(' ', $authorParts[0])[0];
                        if ($authorFirstLastName === $firstLastName && $authorParts[1] === $firstName) {
                            $matches[] = $author;
                        }
                    }
                }
            } else {
                // For non-standard format, match the normalized name as a whole
                $normAuthor = $author['norm_name'];
                if ($normAuthor === $normSource) {
                    $matches[] = $author;
                }
            }
        }

        // Return all matches found
        return $matches;
    }
}