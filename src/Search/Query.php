<?php
/**
 * Copyright 2015 Tom Walder
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */
namespace Search;

/**
 * Search Query
 *
 * @author Tom Walder <tom@docnet.nu>
 */
class Query
{

    const DESC = 'DESCENDING';
    const ASC = 'ASCENDING';

    /**
     * Query string
     *
     * @var string
     */
    private $str_query = '';

    /**
     * Max results
     *
     * @var int
     */
    private $int_limit = 20;

    /**
     *Result offset
     *
     * @var int
     */
    private $int_offset = 0;

    /**
     * A list of the required return fields
     *
     * @var null
     */
    private $arr_return_fields = null;

    /**
     * A list of the required return expressions
     *
     * @var null
     */
    private $arr_return_expressions = [];

    /**
     * Applied sorts
     *
     * @var array
     */
    private $arr_sorts = [];

    /**
     * Set the query string on construction
     *
     * @param $str_query
     */
    public function __construct($str_query = '')
    {
        $this->str_query = $str_query;
    }

    /**
     * Get the query string
     *
     * @return string
     */
    public function getQuery()
    {
        return $this->str_query;
    }

    /**
     * Set the number of results, between 1-100
     *
     * @param $int_limit
     * @return $this
     */
    public function limit($int_limit)
    {
        $this->int_limit = min(max(1, $int_limit), 1000);
        return $this;
    }

    /**
     * Get the limit
     *
     * @return int
     */
    public function getLimit()
    {
        return $this->int_limit;
    }

    /**
     * Set the result offset, 0-n
     *
     * @param $int_offset
     * @return $this
     */
    public function offset($int_offset)
    {
        $this->int_offset = min(max(0, $int_offset), 1000);
        return $this;
    }

    /**
     * Get the offset
     *
     * @return int
     */
    public function getOffset()
    {
        return $this->int_offset;
    }

    /**
     * Sort results by a field in ASCending order
     *
     * @param $str_field
     * @param string $str_direction
     * @return $this
     */
    public function sort($str_field, $str_direction = self::DESC)
    {
        $this->arr_sorts[] = [$str_field, $str_direction];
        return $this;
    }

    /**
     * Sort results by distance from a supplied Lat/Lon. Defaults to nearest first.
     *
     * Also includes the distance (in meters) in the results as an expression
     *
     * @param string $str_field
     * @param array $arr_loc
     * @param string $str_dir
     * @return $this
     */
    public function sortByDistance($str_field, array $arr_loc, $str_dir = self::ASC)
    {
        $str_distance_expression = "distance({$str_field}, geopoint({$arr_loc[0]},{$arr_loc[1]}))";
        $this->sort($str_distance_expression, $str_dir);
        $this->expression('distance_from_' . $str_field, $str_distance_expression);
        return $this;
    }

    /**
     * Get applied sorts
     *
     * @return array
     */
    public function getSorts()
    {
        return $this->arr_sorts;
    }

    /**
     * Set the required return fields
     *
     * @param array $arr_fields
     * @return $this
     */
    public function fields(array $arr_fields)
    {
        $this->arr_return_fields = $arr_fields;
        return $this;
    }

    /**
     * Get the fields to return
     *
     * @return null
     */
    public function getReturnFields()
    {
        return $this->arr_return_fields;
    }

    /**
     * Add an expression to return
     *
     * @param string $str_name
     * @param string $str_expression
     * @return $this
     */
    public function expression($str_name, $str_expression)
    {
        $this->arr_return_expressions[] = [$str_name, $str_expression];
        return $this;
    }

    /**
     * Get the expressions to return
     *
     * @return null
     */
    public function getReturnExpressions()
    {
        return $this->arr_return_expressions;
    }

    // @todo Snippets
    // @todo Cursors
    // @todo Facets

}
