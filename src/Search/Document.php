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
 * Search Document
 *
 * @author Tom Walder <tom@docnet.nu>
 */
class Document
{

    /**
     * Document schema
     *
     * @var Schema
     */
    private $obj_schema = null;

    /**
     * Document ID
     *
     * @var string
     */
    private $str_id = null;

    /**
     * Field Data
     *
     * @var array
     */
    private $arr_data = [];

    /**
     * Optionally set the Schema on construction
     *
     * @param Schema $obj_schema
     */
    public function __construct(Schema $obj_schema = null)
    {
        if(null === $obj_schema) {
            $this->obj_schema = new Schema();
        } else {
            $this->obj_schema = $obj_schema;
        }
    }

    /**
     * Set the document ID
     *
     * @param $str_id
     * @return $this
     */
    public function setId($str_id)
    {
        $this->str_id = $str_id;
        return $this;
    }

    /**
     * The Schema for the Entity, if known.
     *
     * @return Schema|null
     */
    public function getSchema()
    {
        return $this->obj_schema;
    }

    /**
     * Magic setter.. sorry
     *
     * Dynamically update the Schema as required
     *
     * @param $str_field
     * @param $mix_value
     */
    public function __set($str_field, $mix_value)
    {
        if(!$this->obj_schema->hasField($str_field)) {
            $this->obj_schema->addAutoField($str_field, $mix_value);
        }
        $this->arr_data[$str_field] = $mix_value;
    }

    /**
     * Magic getter.. sorry
     *
     * @param $str_key
     * @return null
     */
    public function __get($str_key)
    {
        if(isset($this->arr_data[$str_key])) {
            return $this->arr_data[$str_key];
        }
        return null;
    }

    /**
     * Is a data value set?
     *
     * @param $str_key
     * @return bool
     */
    public function __isset($str_key)
    {
        return isset($this->arr_data[$str_key]);
    }

}