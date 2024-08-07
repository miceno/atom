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

class arSolrRangeQuery extends arSolrAbstractQuery
{
    /**
     * Query Params.
     *
     * @var mixed
     */
    protected $range;

    /**
     * Array of fields to be queried.
     *
     * @var array
     */
    protected $field;

    /**
     * Search query.
     */
    protected string $computedRange = '*';

    /**
     * Constructor.
     *
     * @param mixed $field
     * @param mixed $range
     */
    public function __construct($field, $range)
    {
        $this->setField($field);
        $this->setRange($range);
    }

    public function setField($field)
    {
        $this->field = $field;
    }

    public function getField()
    {
        return $this->field;
    }

    public function setRange($range)
    {
        $computedRange = '';
        if ($range['lte']) {
            $computedRange = "[{$range['lte']} TO ";
        } elseif ($range['lt']) {
            $computedRange = "{{$range['lte']} TO ";
        } else {
            $computedRange = '[* TO ';
        }

        if ($range['gte']) {
            $computedRange .= "{$range['gte']}]";
        } elseif ($range['gt']) {
            $computedRange .= "{$range['gt']}}";
        } else {
            $computedRange .= '*]';
        }

        $this->range = $range;

        $this->computedRange = $computedRange;
    }

    public function getRange()
    {
        return $this->range;
    }

    public function getQueryParams()
    {
        $this->generateQueryParams();

        return $this->query;
    }

    public function generateQueryParams()
    {
        $this->query = [
            'query' => [
                'lucene' => [
                    'query' => "{$this->field}:{$this->computedRange}",
                ],
            ],
            'offset' => $this->offset,
            'limit' => $this->size,
        ];
    }
}
