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
 * Tests for Schema class
 *
 * @author Tom Walder <tom@docnet.nu>
 */
class SchemaTest extends \PHPUnit_Framework_TestCase {

    /**
     * Set up a schema with all data types
     */
    public function testSchema()
    {
        $obj_schema = (new \Search\Schema())
            ->addAtom('atom')
            ->addHtml('html')
            ->addText('text')
            ->addNumber('number')
            ->addDate('date')
            ->addGeopoint('geopoint')
        ;
        $this->assertEquals($obj_schema->getFields(), [
            'atom' => \Search\Schema::FIELD_ATOM,
            'html' => \Search\Schema::FIELD_HTML,
            'text' => \Search\Schema::FIELD_TEXT,
            'number' => \Search\Schema::FIELD_NUMBER,
            'date' => \Search\Schema::FIELD_DATE,
            'geopoint' => \Search\Schema::FIELD_GEOPOINT
        ]);
    }

    /**
     * Test some automated field detection
     */
    public function testAutoField()
    {
        $obj_schema = (new \Search\Schema())
            ->addAutoField('string', 'some string')
            ->addAutoField('number1', 99)
            ->addAutoField('number2', 1.21)
            ->addAutoField('date', new DateTime())
        ;
        $this->assertEquals($obj_schema->getFields(), [
            'string' => \Search\Schema::FIELD_TEXT,
            'number1' => \Search\Schema::FIELD_NUMBER,
            'number2' => \Search\Schema::FIELD_NUMBER,
            'date' => \Search\Schema::FIELD_DATE
        ]);
    }

    /**
     * Check that fields are testable
     */
    public function testHasField()
    {
        $obj_schema = (new \Search\Schema())
            ->addAtom('atom')
            ->addHtml('html')
            ->addAutoField('number', 99)
        ;
        $this->assertTrue($obj_schema->hasField('atom'));
        $this->assertTrue($obj_schema->hasField('html'));
        $this->assertTrue($obj_schema->hasField('number'));
        $this->assertFalse($obj_schema->hasField('missing'));
    }

    /**
     * Basic create doc tests
     */
    public function testCreateDoc()
    {
        $obj_schema = (new \Search\Schema())
            ->addAtom('isbn')
            ->addText('title')
        ;
        $obj_doc = $obj_schema->createDocument([
            'isbn' => '123456789',
            'title' => 'Booky Wooky'
        ]);
        $this->assertInstanceOf('\\Search\\Document', $obj_doc);
    }

}