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
 * Tests for Tokenizer class
 *
 * @author Tom Walder <tom@docnet.nu>
 */
class TokenizerTest extends \PHPUnit_Framework_TestCase
{

    /**
     * Can we create a Tokenizer?
     */
    public function testExists()
    {
        $obj_tkzr = new \Search\Tokenizer();
        $this->assertInstanceOf('\\Search\\Tokenizer', $obj_tkzr);
    }

    public function testBasicOneWord()
    {
        $obj_tkzr = new \Search\Tokenizer();
        $this->assertEquals('test t te tes', $obj_tkzr->edgeNGram('test'));
    }

    public function testBasicTwoWord()
    {
        $obj_tkzr = new \Search\Tokenizer();
        $this->assertEquals('two words t tw w wo wor word', $obj_tkzr->edgeNGram('two words'));
    }

    public function testTwoWordMinLen()
    {
        $obj_tkzr = new \Search\Tokenizer();
        $this->assertEquals('two words tw wo wor word', $obj_tkzr->edgeNGram('two words', 2));
        $this->assertEquals('two words wor word', $obj_tkzr->edgeNGram('two words', 3));
    }

    public function testAlpha()
    {
        $obj_tkzr = new \Search\Tokenizer();
        $this->assertEquals('test 123 t te tes 1 12', $obj_tkzr->edgeNGram('test 123'));
    }

}