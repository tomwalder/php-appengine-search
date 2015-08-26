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
 * Search Index
 *
 * @author Tom Walder <tom@docnet.nu>
 */
class Index
{

    /**
     * Index name
     *
     * @var string
     */
    private $str_index_name = null;

    /**
     * @var Gateway
     */
    private $obj_gateway = null;

    /**
     * @param $str_index_name
     */
    public function __construct($str_index_name)
    {
        $this->str_index_name = $str_index_name;
        $this->obj_gateway = new Gateway($str_index_name);
    }

    /**
     * Put a document into the index
     *
     * @param Document|Document[] $docs
     */
    public function put($docs)
    {
        if($docs instanceof Document) {
            $this->obj_gateway->put([$docs]);
        } elseif (is_array($docs)) {
            $this->obj_gateway->put($docs);
        } else {
            throw new \InvalidArgumentException('Parameter must be one or more \Search\Document objects');
        }
    }

    /**
     * Run a basic search query
     *
     * @todo support Query objects - or another method, query() - TBC
     *
     * @param $str_phrase
     * @return array
     */
    public function search($str_phrase)
    {
        return $this->obj_gateway->search($str_phrase);
    }

    /**
     * Get a single document by ID
     *
     * @param $str_id
     * @return array
     */
    public function get($str_id)
    {
        return $this->obj_gateway->getDocById($str_id);
    }

    /**
     * Return some debug info
     *
     * @return array
     */
    public function debug()
    {
        return [$this->obj_gateway->getLastRequest(), $this->obj_gateway->getLastResponse()];
    }

    // @todo DeleteDocumentRequest
    // @todo ListIndexesRequest
    // @todo DeleteIndexRequest
    // @todo RequestStatus
    // @todo ListDocumentsRequest
    // @todo CancelDeleteIndexRequest
    // @todo DeleteSchemaRequest
    // @todo FacetRequestParam
    // @todo FacetRequest

}