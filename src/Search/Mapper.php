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

use storage_onestore_v3\Document as GoogleDocument;
use storage_onestore_v3\FieldValue\ContentType;
use storage_onestore_v3\Field;

/**
 * Mapper
 *
 * @author Tom Walder <tom@docnet.nu>
 */
class Mapper
{

    /**
     * Type map (to Google)
     *
     * @var array
     */
    private static $arr_types = [
        Schema::FIELD_ATOM => ContentType::ATOM,
        Schema::FIELD_TEXT => ContentType::TEXT,
        Schema::FIELD_HTML => ContentType::HTML,
        Schema::FIELD_NUMBER => ContentType::NUMBER,
        Schema::FIELD_DATE => ContentType::DATE,
        Schema::FIELD_GEOPOINT => ContentType::GEO
    ];

    /**
     * Type map (from Google)
     *
     * @var array
     */
    private static $arr_types_rev = [];

    /**
     * Create the reverse type map
     */
    public function __construct()
    {
        self::$arr_types_rev = array_flip(self::$arr_types);
    }

    /**
     * Map from a Google document to a Search Document
     *
     * @param GoogleDocument $obj_source
     * @return Document
     */
    public function fromGoogle(GoogleDocument $obj_source)
    {
        $obj_schema = new Schema();
        $obj_doc = new Document($obj_schema);
        $obj_doc->setId($obj_source->getId());
        foreach($obj_source->getFieldList() as $obj_field) {
            /** @var Field $obj_field */
            $str_field_name = $obj_field->getName();
            $obj_value = $obj_field->getValue();
            if(ContentType::GEO === $obj_value->getType()) {
                $obj_schema->addGeopoint($str_field_name);
                $obj_geo = $obj_value->getGeo();
                $obj_doc->{$str_field_name} = [$obj_geo->getLat(), $obj_geo->getLng()];
            } else {
                if(isset(self::$arr_types_rev[$obj_value->getType()])) {
                    $obj_schema->addField($str_field_name, self::$arr_types_rev[$obj_value->getType()]);
                    $obj_doc->{$str_field_name} = $obj_value->getStringValue();
                } else {
                    throw new \InvalidArgumentException('Unknown type mapping from Google Document');
                }
            }
        }
        return $obj_doc;
    }

    /**
     * Map from a Google document to a Search Document
     *
     * @param Document $obj_source
     * @param GoogleDocument $obj_target
     * @return GoogleDocument
     */
    public function toGoogle(Document $obj_source, GoogleDocument $obj_target)
    {
        $obj_target->setId($obj_source->getId());
        foreach($obj_source->getSchema()->getFields() as $str_name => $int_type) {
            $obj_value = $obj_target->addField()->setName($str_name)->mutableValue();
            if(Schema::FIELD_GEOPOINT === $int_type) {
                $obj_value->setType(ContentType::GEO);
                if(isset($obj_source->{$str_name}) && is_array($obj_source->{$str_name})) {
                    $arr_geo = $obj_source->{$str_name};
                    $obj_value->mutableGeo()->setLat($arr_geo[0])->setLng($arr_geo[1]);
                } else {
                    throw new \InvalidArgumentException('Geopoint data is required');
                }
            } else {
                if(isset(self::$arr_types[$int_type])) {
                    $obj_value->setType(self::$arr_types[$int_type]);
                } else {
                    throw new \InvalidArgumentException('Unknown type mapping to Google Document');
                }
                // @todo deal with specific field types requiring null / blanks / values
                if(isset($obj_source->{$str_name})) {
                    $mix_val = $obj_source->{$str_name};
                    if($mix_val instanceof \DateTime) {
                        $obj_value->setStringValue($mix_val->format('Y-m-d'));
                    } else {
                        $obj_value->setStringValue((string)$mix_val);
                    }
                } else {
                    $obj_value->setStringValue(null);
                }
            }
        }
        return $obj_target;
    }

}
