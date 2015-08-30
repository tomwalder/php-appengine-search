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

/**
 * Tests for Query class
 *
 * @author Tom Walder <tom@docnet.nu>
 */
class QueryTest extends \PHPUnit_Framework_TestCase
{

    /**
     * Set setting and getting the query string
     */
    public function testQueryString()
    {
        $obj_query = new \Search\Query('test string here');
        $this->assertEquals('test string here', $obj_query->getQuery());
    }

    /**
     * Test getting and setting the limit
     */
    public function testLimit()
    {
        $obj_query = new \Search\Query();
        $this->assertEquals(20, $obj_query->getLimit());
        $obj_query->limit(121);
        $this->assertEquals(121, $obj_query->getLimit());
        $obj_query->limit(-10);
        $this->assertEquals(1, $obj_query->getLimit());
        $obj_query2 = $obj_query->limit(2001);
        $this->assertEquals(1000, $obj_query->getLimit());
        $this->assertSame($obj_query, $obj_query2);
    }

    /**
     * Test getting and setting the offset
     */
    public function testOffset()
    {
        $obj_query = new \Search\Query();
        $this->assertEquals(0, $obj_query->getOffset());
        $obj_query->offset(121);
        $this->assertEquals(121, $obj_query->getOffset());
        $obj_query->offset(-10);
        $this->assertEquals(0, $obj_query->getOffset());
        $obj_query2 = $obj_query->offset(2001);
        $this->assertEquals(1000, $obj_query->getOffset());
        $this->assertSame($obj_query, $obj_query2);
    }

    /**
     * Test sort setter & getter
     */
    public function testSort()
    {
        $obj_query = new \Search\Query();
        $this->assertEmpty($obj_query->getSorts());
        $obj_query->sort('a', 'b');
        $this->assertEquals([['a', 'b']], $obj_query->getSorts());
        $obj_query2 = $obj_query->sort('c', 'd');
        $this->assertEquals([['a', 'b'],['c', 'd']], $obj_query->getSorts());
        $this->assertSame($obj_query, $obj_query2);
    }

    /**
     * Test return field config
     */
    public function testFields()
    {
        $obj_query = new \Search\Query();
        $this->assertEmpty($obj_query->getReturnFields());
        $obj_query->fields(['a', 'b']);
        $this->assertEquals(['a', 'b'], $obj_query->getReturnFields());
        $obj_query2 = $obj_query->fields(['c', 'd']);
        $this->assertEquals(['c', 'd'], $obj_query->getReturnFields());
        $this->assertSame($obj_query, $obj_query2);
    }

    /**
     * Test expressions
     */
    public function testExpression()
    {
        $obj_query = new \Search\Query();
        $this->assertEmpty($obj_query->getReturnExpressions());
        $obj_query->expression('a', 'b');
        $this->assertEquals([['a', 'b']], $obj_query->getReturnExpressions());
        $obj_query2 = $obj_query->expression('c', 'd');
        $this->assertEquals([['a', 'b'],['c', 'd']], $obj_query->getReturnExpressions());
        $this->assertSame($obj_query, $obj_query2);
    }

    /**
     * Test the distance helper
     */
    public function testDistance()
    {
        $obj_query = new \Search\Query();
        $this->assertEmpty($obj_query->getSorts());
        $this->assertEmpty($obj_query->getReturnExpressions());
        $obj_query2 = $obj_query->sortByDistance('where', [1.21, 3.14159]);
        $this->assertEquals([['distance(where, geopoint(1.21,3.14159))', \Search\Query::ASC]], $obj_query->getSorts());
        $this->assertEquals([['distance_from_where', 'distance(where, geopoint(1.21,3.14159))']], $obj_query->getReturnExpressions());
        $this->assertSame($obj_query, $obj_query2);
    }

}