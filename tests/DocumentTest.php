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
 * Tests for Document class
 *
 * @author Tom Walder <tom@docnet.nu>
 */
class DocumentTest extends \PHPUnit_Framework_TestCase
{

    /**
     * Does the Document create it's own Schema?
     */
    public function testAutoSchema()
    {
        $obj_doc = new \Search\Document();
        $this->assertInstanceOf('\\Search\\Schema', $obj_doc->getSchema());
    }

    /**
     * Does the Document retain the Schema we give it?
     */
    public function testManualSchema()
    {
        $obj_schema = (new \Search\Schema())->addText('testing123');
        $obj_doc = new \Search\Document($obj_schema);
        $this->assertInstanceOf('\\Search\\Schema', $obj_doc->getSchema());
        $this->assertSame($obj_schema, $obj_doc->getSchema());
    }

    /**
     * Can we set and get IDs. Is the interface fluent?
     */
    public function testId()
    {
        $obj_doc = new \Search\Document();
        $obj_doc2 = $obj_doc->setId('121gw');
        $this->assertEquals('121gw', $obj_doc->getId());
        $this->assertSame($obj_doc, $obj_doc2);
    }

    /**
     * Set and Get some data
     */
    public function testMagicSetterGetter()
    {
        $obj_doc = new \Search\Document();
        $obj_doc->power = '121gw';
        $this->assertTrue(isset($obj_doc->power));
        $this->assertFalse(isset($obj_doc->missing));
        $this->assertEquals('121gw', $obj_doc->power);
        $this->assertNull($obj_doc->missing);
    }

    /**
     * Can we get all the data?
     */
    public function testGetData()
    {
        $obj_doc = new \Search\Document();
        $obj_doc->power = '121gw';
        $this->assertEquals(['power' => '121gw'], $obj_doc->getData());
    }

    /**
     * Can we set and return Expressions?
     */
    public function testExpressionGetSet()
    {
        $obj_doc = new \Search\Document();
        $obj_doc->setExpression('a', 'b');
        $obj_doc->setExpression('c', 'd');

        $this->assertEquals('b', $obj_doc->getExpression('a'));
        $this->assertNull($obj_doc->getExpression('missing'));
        $this->assertEquals(['a' => 'b', 'c' => 'd'], $obj_doc->getExpressions());
    }

}