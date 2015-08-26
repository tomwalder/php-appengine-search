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

use google\appengine\IndexDocumentRequest;
use google\appengine\IndexDocumentResponse;
use google\appengine\runtime\ApiProxy;
use google\appengine\runtime\ApplicationError;
use google\appengine\SearchRequest;
use google\appengine\SearchResponse;
use google\appengine\SearchResult;
use google\appengine\SearchServiceError\ErrorCode;
use google\net\ProtocolMessage;

/**
 * Search Gateway
 *
 * @author Tom Walder <tom@docnet.nu>
 */
class Gateway
{

    /**
     * The index name
     *
     * @var string
     */
    protected $str_index_name = null;

    /**
     * The last request
     *
     * @var ProtocolMessage
     */
    protected $obj_last_request = null;

    /**
     * The last response
     *
     * @var ProtocolMessage
     */
    protected $obj_last_response = null;

    /**
     * Set the index name
     *
     * @param string $str_index_name
     */
    public function __construct($str_index_name)
    {
        $this->str_index_name = $str_index_name;
    }

    /**
     * Put one or more documents into the index
     *
     * @param Document[] $arr_docs
     * @throws ApplicationError
     * @throws \Exception
     */
    public function put(array $arr_docs)
    {
        $obj_request = new IndexDocumentRequest();
        $obj_params = $obj_request->mutableParams();
        $obj_params->mutableIndexSpec()->setName($this->str_index_name);
        // Other index specs: consistency, mode, name, namespace, source, version
        $obj_mapper = new Mapper();
        foreach($arr_docs as $obj_doc) {
            $obj_mapper->toGoogle($obj_doc, $obj_params->addDocument());
        }
        $this->execute('IndexDocument', $obj_request, new IndexDocumentResponse());
    }

    /**
     * Run a Search Query
     *
     * @param Query $obj_query
     * @return array
     * @throws ApplicationError
     * @throws \Exception
     */
    public function search(Query $obj_query)
    {
        $obj_request = new SearchRequest();
        $obj_params = $obj_request->mutableParams();
        $obj_params->mutableIndexSpec()->setName($this->str_index_name);
        // Other index specs: consistency, mode, name, namespace, source, version

        // Basics
        $obj_request->getParams()
            ->setQuery($obj_query->getQuery())
            ->setLimit($obj_query->getLimit())
            ->setOffset($obj_query->getOffset())
        ;

        // Sorting
        $arr_sorts = $obj_query->getSorts();
        if(null !== $arr_sorts && count($arr_sorts) > 0) {
            foreach ($arr_sorts as $arr_sort) {
                $obj_sort = $obj_params->addSortSpec();
                $obj_sort->setSortExpression($arr_sort[0]);
                $obj_sort->setSortDescending(Query::DESC === $arr_sort[1]);
            }
        }

        // Return Fields
        $arr_return_fields = $obj_query->getReturnFields();
        if(null !== $arr_return_fields && count($arr_return_fields) > 0) {
            $obj_fields = $obj_params->mutableFieldSpec();
            foreach ($arr_return_fields as $str_field) {
                $obj_fields->addName($str_field);
            }
        }


        $this->execute('Search', $obj_request, new SearchResponse());
        return $this->processSearchResponse();
    }

    /**
     * @todo Return a single document by ID
     *
     * @param $str_id
     * @return array
     * @throws ApplicationError
     * @throws \Exception
     */
    public function getDocById($str_id)
    {
        // @todo
    }

    /**
     * Run a Request
     *
     * @param $str_method
     * @param ProtocolMessage $obj_request
     * @param ProtocolMessage $obj_response
     * @return ProtocolMessage|null|object
     * @throws ApplicationError
     * @throws \Exception
     */
    private function execute($str_method, ProtocolMessage $obj_request, ProtocolMessage $obj_response)
    {
        try {
            $this->obj_last_request = $obj_request;
            $this->obj_last_response = null;
            ApiProxy::makeSyncCall('search', $str_method, $obj_request, $obj_response, 60);
            $this->obj_last_response = $obj_response;
        } catch (ApplicationError $obj_exception) {
            throw $obj_exception;
        }
    }

    /**
     * Process a search response
     *
     * @return array
     */
    private function processSearchResponse()
    {
        /** @var SearchResponse $obj_search_response */
        $obj_search_response = $this->obj_last_response;
        $obj_response = (object)[
            'status' => $this->describeStatusCode($obj_search_response->getStatus()->getCode()),
            'hits' => $obj_search_response->getMatchedCount(),
            'count' => $obj_search_response->getResultSize(),
            'results' => []
        ];
        $obj_mapper = new Mapper();
        foreach($obj_search_response->getResultList() as $obj_result) {
            /** @var SearchResult $obj_result */
            $obj_doc = $obj_mapper->fromGoogle($obj_result->getDocument());
            $obj_response->results[] = (object)[
                '_id' => $obj_result->getDocument()->getId(),
                'score' => null, // $obj_result->getScore()
                'doc' => $obj_doc
            ];
        }
        return $obj_response;
    }

    /**
     * Describe a request/response status
     *
     * @param $int_code
     * @return string
     */
    private function describeStatusCode($int_code)
    {
        $arr_codes = [
            ErrorCode::OK => 'OK',
            ErrorCode::INVALID_REQUEST => 'INVALID_REQUEST',
            ErrorCode::TRANSIENT_ERROR => 'TRANSIENT_ERROR',
            ErrorCode::INTERNAL_ERROR => 'INTERNAL_ERROR',
            ErrorCode::PERMISSION_DENIED => 'PERMISSION_DENIED',
            ErrorCode::TIMEOUT => 'TIMEOUT',
            ErrorCode::CONCURRENT_TRANSACTION => 'CONCURRENT_TRANSACTION'
        ];
        if(isset($arr_codes[$int_code])) {
            return $arr_codes[$int_code];
        }
        return 'UNKNOWN';
    }

    /**
     * @return ProtocolMessage
     */
    public function getLastResponse()
    {
        return $this->obj_last_response;
    }

    /**
     * @return ProtocolMessage
     */
    public function getLastRequest()
    {
        return $this->obj_last_request;
    }

}