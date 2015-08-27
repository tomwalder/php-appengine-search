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
 * Search Document Schema
 *
 * @author Tom Walder <tom@docnet.nu>
 */
class Schema
{

    /**
     * Field data types
     *
     * Atom Field - an indivisible character string
     * Text Field - a plain text string that can be searched word by word
     * HTML Field - a string that contains HTML markup tags, only the text outside the markup tags can be searched
     * Number Field - a floating point number
     * Date Field - a date object with year/month/day and optional time
     * Geopoint Field - a data object with latitude and longitude coordinates
     */
    const FIELD_ATOM = 1;
    const FIELD_TEXT = 2;
    const FIELD_HTML = 3;
    const FIELD_NUMBER = 4;
    const FIELD_DATE = 5;
    const FIELD_GEOPOINT = 20;
    const FIELD_DETECT = 99; // used for auto-detection

    /**
     * Known fields
     *
     * @var array
     */
    private $arr_defined_fields = [];

    /**
     * Add a field to the known field array
     *
     * @param $str_name
     * @param $int_type
     * @return $this
     */
    public function addField($str_name, $int_type)
    {
        $this->arr_defined_fields[$str_name] = $int_type;
        return $this;
    }

    /**
     * Add an ATOM field to the schema
     *
     * Atom Field - an indivisible character string
     *
     * @param $str_name
     * @return Schema
     */
    public function addAtom($str_name)
    {
        return $this->addField($str_name, self::FIELD_ATOM);
    }

    /**
     * Add a TEXT field to the schema
     *
     * Text Field - a plain text string that can be searched word by word
     *
     * @param $str_name
     * @return Schema
     */
    public function addText($str_name)
    {
        return $this->addField($str_name, self::FIELD_TEXT);
    }

    /**
     * Add an HTML field to the schema
     *
     * HTML Field - a string that contains HTML markup tags, only the text outside the markup tags can be searched
     *
     * @param $str_name
     * @return Schema
     */
    public function addHtml($str_name)
    {
        return $this->addField($str_name, self::FIELD_HTML);
    }

    /**
     * Add a NUMBER field to the schema
     *
     * Number Field - a floating point number
     *
     * @param $str_name
     * @return Schema
     */
    public function addNumber($str_name)
    {
        return $this->addField($str_name, self::FIELD_NUMBER);
    }

    /**
     * Add a DATE field to the schema
     *
     * Date Field - a date object with year/month/day
     *
     * @param $str_name
     * @return Schema
     */
    public function addDate($str_name)
    {
        return $this->addField($str_name, self::FIELD_DATE);
    }

    /**
     * Add a GEOPOINT field to the schema
     *
     * Geopoint Field - a data object with latitude and longitude coordinates
     *
     * @param $str_name
     * @return Schema
     */
    public function addGeopoint($str_name)
    {
        return $this->addField($str_name, self::FIELD_GEOPOINT);
    }

    /**
     * Get the configured fields
     *
     * @return array
     */
    public function getFields()
    {
        return $this->arr_defined_fields;
    }

    /**
     * Create and return a Document with this Schema
     *
     * Optionally populate with the supplied data
     *
     * @param array|null $arr_data
     * @return Document
     */
    public function createDocument(array $arr_data = null)
    {
        $obj_doc = new Document($this);
        if(null !== $arr_data) {
            foreach ($arr_data as $str_field => $mix_value) {
                $obj_doc->__set($str_field, $mix_value);
            }
        }
        return $obj_doc;
    }

    /**
     * Check if we have a field defined
     *
     * @param $str_name
     * @return bool
     */
    public function hasField($str_name)
    {
        return isset($this->arr_defined_fields[$str_name]);
    }

    /**
     * Determine field type automatically
     *
     * @param $str_name
     * @param $mix_value
     * @return $this
     */
    public function addAutoField($str_name, $mix_value)
    {
        switch(gettype($mix_value)) {
            case 'integer':
            case 'double':
                $this->addNumber($str_name);
                break;

            case 'object':
                if($mix_value instanceof \DateTime) {
                    $this->addDate($str_name);
                } elseif (method_exists($mix_value, '__toString')) {
                    $this->addText($str_name);
                } else {
                    // @todo consider exception
                }
                break;

            case 'array': // @todo consider exception
            case 'string':
            case 'boolean': // @todo consider exception / numeric
            case 'resource': // @todo consider exception
            case 'null':
            case 'unknown type': // @todo consider exception
            default:
                $this->addText($str_name);
        }
        return $this;
    }

}