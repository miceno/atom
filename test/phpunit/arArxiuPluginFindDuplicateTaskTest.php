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

use PHPUnit\Framework\TestCase;

/**
 * Test class for arArxiuPluginFindDuplicateTask matching algorithm.
 *
 * @internal
 */
class arArxiuPluginFindDuplicateTaskTest extends TestCase
{
    /**
     * Test harness class instance for accessing protected methods.
     */
    protected $testHarness;

    public function setUp(): void
    {
        $this->testHarness = new ArArxiuPluginFindDuplicateTaskTestHarness();
    }

    // =========================================================================
    // normalizeName() tests
    // =========================================================================

    /**
     * @dataProvider normalizeNameProvider
     */
    public function testNormalizeName(string $input, string $expected): void
    {
        $result = $this->testHarness->normalizeName($input);
        $this->assertSame($expected, $result);
    }

    public function normalizeNameProvider(): array
    {
        return [
            // Basic normalization: lowercase
            ['Smith, John', 'smith, john'],

            // Accent normalization
            ['García, José', 'garcia, jose'],
            ['Müller, Hans', 'muller, hans'],
            ['Çelik, Ömer', 'celik, omer'],
            ['Björk, Sigríður', 'bjork, sigridur'],

            // Parentheses removal
            ['Smith, John (Jr.)', 'smith, john'],
            ['García, José (the Elder)', 'garcia, jose'],
            ['Martinez (editor), Ana', 'martinez, ana'],

            // Multiple spaces normalization
            ['Smith,   John', 'smith, john'],
            ['García,  José   María', 'garcia, jose maria'],

            // Non-alphanumeric characters removal (except comma)
            ['Smith-Jones, John', 'smithjones, john'],
            ['O\'Brien, Patrick', 'obrien, patrick'],
            ['de la Cruz, María', 'de la cruz, maria'],

            // Complex cases
            ['García López, José María', 'garcia lopez, jose maria'],
            ['Van der Berg, Jan', 'van der berg, jan'],
            ['McDonald, Ronald (III)', 'mcdonald, ronald'],

            // Non-standard format (no comma)
            ['John Smith', 'john smith'],
            ['José García López', 'jose garcia lopez'],

            // Edge cases
            ['', ''],
            ['   ', ''],
            ['Smith', 'smith'],
        ];
    }

    // =========================================================================
    // matchAuthor() tests
    // =========================================================================

    /**
     * Test exact match for name with comma (standard format).
     */
    public function testMatchAuthorExactMatchWithComma(): void
    {
        $sourceAuthor = [
            'actorId' => 1,
            'name' => 'Smith, John',
            'nameId' => null,
            'norm_name' => 'smith, john',
        ];

        $authorList = [
            [
                'actorId' => 2,
                'name' => 'Smith, John',
                'nameId' => null,
                'norm_name' => 'smith, john',
            ],
            [
                'actorId' => 3,
                'name' => 'Jones, Mary',
                'nameId' => null,
                'norm_name' => 'jones, mary',
            ],
        ];

        $normNameIndex = $this->buildNormNameIndex($authorList);

        $matches = $this->testHarness->matchAuthor($sourceAuthor, $authorList, $normNameIndex);

        $this->assertCount(1, $matches);
        $this->assertSame(2, $matches[0]['actorId']);
    }

    /**
     * Test match for name with double last name against single last name.
     */
    public function testMatchAuthorDoubleLastNameMatchesSingle(): void
    {
        $sourceAuthor = [
            'actorId' => 1,
            'name' => 'García López, José',
            'nameId' => null,
            'norm_name' => 'garcia lopez, jose',
        ];

        $authorList = [
            [
                'actorId' => 2,
                'name' => 'García, José',
                'nameId' => null,
                'norm_name' => 'garcia, jose',
            ],
            [
                'actorId' => 3,
                'name' => 'Smith, John',
                'nameId' => null,
                'norm_name' => 'smith, john',
            ],
        ];

        $normNameIndex = $this->buildNormNameIndex($authorList);

        $matches = $this->testHarness->matchAuthor($sourceAuthor, $authorList, $normNameIndex);

        $this->assertCount(1, $matches);
        $this->assertSame(2, $matches[0]['actorId']);
    }

    /**
     * Test that a single last name matches a double last name entry.
     */
    public function testMatchAuthorSingleLastNameMatchesDouble(): void
    {
        $sourceAuthor = [
            'actorId' => 1,
            'name' => 'García, José',
            'nameId' => null,
            'norm_name' => 'garcia, jose',
        ];

        $authorList = [
            [
                'actorId' => 2,
                'name' => 'García López, José',
                'nameId' => null,
                'norm_name' => 'garcia lopez, jose',
            ],
            [
                'actorId' => 3,
                'name' => 'García, José',
                'nameId' => null,
                'norm_name' => 'garcia, jose',
            ],
        ];

        $normNameIndex = $this->buildNormNameIndex($authorList);

        $matches = $this->testHarness->matchAuthor($sourceAuthor, $authorList, $normNameIndex);

        // Should find the exact match only (actorId 3)
        // The single last name won't match double last name in the current implementation
        $this->assertCount(1, $matches);
        $this->assertSame(3, $matches[0]['actorId']);
    }

    /**
     * Test match for name without comma (non-standard format).
     */
    public function testMatchAuthorWithoutComma(): void
    {
        $sourceAuthor = [
            'actorId' => 1,
            'name' => 'John Smith',
            'nameId' => null,
            'norm_name' => 'john smith',
        ];

        $authorList = [
            [
                'actorId' => 2,
                'name' => 'John Smith',
                'nameId' => null,
                'norm_name' => 'john smith',
            ],
            [
                'actorId' => 3,
                'name' => 'Jane Doe',
                'nameId' => null,
                'norm_name' => 'jane doe',
            ],
        ];

        $normNameIndex = $this->buildNormNameIndex($authorList);

        $matches = $this->testHarness->matchAuthor($sourceAuthor, $authorList, $normNameIndex);

        $this->assertCount(1, $matches);
        $this->assertSame(2, $matches[0]['actorId']);
    }

    /**
     * Test that same actorId is skipped.
     */
    public function testMatchAuthorSkipsSameActorId(): void
    {
        $sourceAuthor = [
            'actorId' => 1,
            'name' => 'Smith, John',
            'nameId' => null,
            'norm_name' => 'smith, john',
        ];

        $authorList = [
            [
                'actorId' => 1,  // Same actorId as source
                'name' => 'Smith, John',
                'nameId' => null,
                'norm_name' => 'smith, john',
            ],
            [
                'actorId' => 2,
                'name' => 'Smith, John',
                'nameId' => null,
                'norm_name' => 'smith, john',
            ],
        ];

        $normNameIndex = $this->buildNormNameIndex($authorList);

        $matches = $this->testHarness->matchAuthor($sourceAuthor, $authorList, $normNameIndex);

        $this->assertCount(1, $matches);
        $this->assertSame(2, $matches[0]['actorId']);
    }

    /**
     * Test no match found.
     */
    public function testMatchAuthorNoMatch(): void
    {
        $sourceAuthor = [
            'actorId' => 1,
            'name' => 'Smith, John',
            'nameId' => null,
            'norm_name' => 'smith, john',
        ];

        $authorList = [
            [
                'actorId' => 2,
                'name' => 'Jones, Mary',
                'nameId' => null,
                'norm_name' => 'jones, mary',
            ],
            [
                'actorId' => 3,
                'name' => 'Williams, Robert',
                'nameId' => null,
                'norm_name' => 'williams, robert',
            ],
        ];

        $normNameIndex = $this->buildNormNameIndex($authorList);

        $matches = $this->testHarness->matchAuthor($sourceAuthor, $authorList, $normNameIndex);

        $this->assertCount(0, $matches);
    }

    /**
     * Test multiple matches found.
     */
    public function testMatchAuthorMultipleMatches(): void
    {
        $sourceAuthor = [
            'actorId' => 1,
            'name' => 'Smith, John',
            'nameId' => null,
            'norm_name' => 'smith, john',
        ];

        $authorList = [
            [
                'actorId' => 2,
                'name' => 'Smith, John',
                'nameId' => null,
                'norm_name' => 'smith, john',
            ],
            [
                'actorId' => 3,
                'name' => 'SMITH, JOHN',  // Different original but same normalized
                'nameId' => null,
                'norm_name' => 'smith, john',
            ],
            [
                'actorId' => 4,
                'name' => 'Jones, Mary',
                'nameId' => null,
                'norm_name' => 'jones, mary',
            ],
        ];

        $normNameIndex = $this->buildNormNameIndex($authorList);

        $matches = $this->testHarness->matchAuthor($sourceAuthor, $authorList, $normNameIndex);

        $this->assertCount(2, $matches);
        $actorIds = array_column($matches, 'actorId');
        $this->assertContains(2, $actorIds);
        $this->assertContains(3, $actorIds);
    }

    /**
     * Test match with accented characters (normalized).
     */
    public function testMatchAuthorAccentedNames(): void
    {
        $sourceAuthor = [
            'actorId' => 1,
            'name' => 'García, José',
            'nameId' => null,
            'norm_name' => 'garcia, jose',
        ];

        $authorList = [
            [
                'actorId' => 2,
                'name' => 'Garcia, Jose',  // Without accents
                'nameId' => null,
                'norm_name' => 'garcia, jose',
            ],
        ];

        $normNameIndex = $this->buildNormNameIndex($authorList);

        $matches = $this->testHarness->matchAuthor($sourceAuthor, $authorList, $normNameIndex);

        $this->assertCount(1, $matches);
        $this->assertSame(2, $matches[0]['actorId']);
    }

    /**
     * Test match for double last name matches both full and first-last-name variants.
     */
    public function testMatchAuthorDoubleLastNameMatchesBothVariants(): void
    {
        $sourceAuthor = [
            'actorId' => 1,
            'name' => 'García López, José',
            'nameId' => null,
            'norm_name' => 'garcia lopez, jose',
        ];

        $authorList = [
            [
                'actorId' => 2,
                'name' => 'García López, José',  // Full match
                'nameId' => null,
                'norm_name' => 'garcia lopez, jose',
            ],
            [
                'actorId' => 3,
                'name' => 'García, José',  // First last name match
                'nameId' => null,
                'norm_name' => 'garcia, jose',
            ],
        ];

        $normNameIndex = $this->buildNormNameIndex($authorList);

        $matches = $this->testHarness->matchAuthor($sourceAuthor, $authorList, $normNameIndex);

        $this->assertCount(2, $matches);
        $actorIds = array_column($matches, 'actorId');
        $this->assertContains(2, $actorIds);
        $this->assertContains(3, $actorIds);
    }

    /**
     * Test empty author list returns no matches.
     */
    public function testMatchAuthorEmptyList(): void
    {
        $sourceAuthor = [
            'actorId' => 1,
            'name' => 'Smith, John',
            'nameId' => null,
            'norm_name' => 'smith, john',
        ];

        $authorList = [];
        $normNameIndex = [];

        $matches = $this->testHarness->matchAuthor($sourceAuthor, $authorList, $normNameIndex);

        $this->assertCount(0, $matches);
    }

    /**
     * Build a norm_name index from an author list.
     */
    protected function buildNormNameIndex(array $authorList): array
    {
        $normNameIndex = [];
        foreach ($authorList as $author) {
            $norm = $author['norm_name'];
            if (!isset($normNameIndex[$norm])) {
                $normNameIndex[$norm] = [];
            }
            $normNameIndex[$norm][] = $author;
        }

        return $normNameIndex;
    }
}

/**
 * Test harness class to expose protected methods for testing.
 */
class ArArxiuPluginFindDuplicateTaskTestHarness
{
    /**
     * Normalize a name for duplicate detection.
     * (Copy of the protected method from arArxiuPluginFindDuplicateTask)
     */
    public function normalizeName(string $name): string
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

    /**
     * Match a source author object to a list of author objects using an index for optimization.
     * (Copy of the protected method from arArxiuPluginFindDuplicateTask)
     */
    public function matchAuthor(array $sourceAuthor, array $authorList, array $normNameIndex): array
    {
        // Use the normalized name from the source author object
        $normSource = $sourceAuthor['norm_name'];

        // Initialize an array to hold matches
        $matches = [];

        // Skip authors with the same actorId as the source author in all cases
        $skipId = $sourceAuthor['actorId'];

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
                // Build possible norm_name keys for matching
                $fullNorm = $lastNames . ', ' . $firstName;
                $firstLastName = explode(' ', $lastNames)[0];
                $firstNorm = $firstLastName . ', ' . $firstName;
                // Check for matches in the index for full last name
                if (isset($normNameIndex[$fullNorm])) {
                    foreach ($normNameIndex[$fullNorm] as $author) {
                        if ($author['actorId'] !== $skipId) {
                            $matches[] = $author;
                        }
                    }
                }
                // Check for matches in the index for first last name
                if (isset($normNameIndex[$firstNorm])) {
                    foreach ($normNameIndex[$firstNorm] as $author) {
                        if ($author['actorId'] !== $skipId) {
                            $matches[] = $author;
                        }
                    }
                }
            }
        } else {
            // For non-standard format, match the normalized name as a whole using the index
            if (isset($normNameIndex[$normSource])) {
                foreach ($normNameIndex[$normSource] as $author) {
                    if ($author['actorId'] !== $skipId) {
                        $matches[] = $author;
                    }
                }
            }
        }

        // Return all matches found
        return $matches;
    }
}

