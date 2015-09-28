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
     * @var Gateway
     */
    private $obj_gateway = null;

    /**
     * @param $str_index_name
     * @param null $str_namespace
     */
    public function __construct($str_index_name, $str_namespace = null)
    {
        $this->obj_gateway = new Gateway($str_index_name, $str_namespace);
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
     * Support simple query strings OR Query objects
     *
     * @param $query
     * @return object
     */
    public function search($query)
    {
        if($query instanceof Query) {
            return $this->obj_gateway->search($query);
        }
        return $this->obj_gateway->search(new Query((string)$query));
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
     * Delete one or more documents
     *
     * @param $docs
     */
    public function delete($docs)
    {
        if($docs instanceof Document) {
            $this->obj_gateway->delete([$docs->getId()]);
        } elseif (is_string($docs)) {
            $this->obj_gateway->delete([$docs]);
        } elseif (is_array($docs)) {
            $arr_doc_ids = [];
            foreach($docs as $doc){
                if($doc instanceof Document) {
                    $arr_doc_ids[] = $doc->getId();
                } elseif (is_string($doc)) {
                    $arr_doc_ids[] = $doc;
                } else {
                    throw new \InvalidArgumentException('Parameter must be one or more \Search\Document objects or ID strings');
                }
            }
            $this->obj_gateway->delete($arr_doc_ids);
        } else {
            throw new \InvalidArgumentException('Parameter must be one or more \Search\Document objects or ID strings');
        }
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

    // @todo FacetRequestParam
    // @todo FacetRequest

}