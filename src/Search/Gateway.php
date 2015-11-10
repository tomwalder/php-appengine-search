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

use google\appengine\DeleteDocumentRequest;
use google\appengine\DeleteDocumentResponse;
use google\appengine\IndexDocumentRequest;
use google\appengine\IndexDocumentResponse;
use google\appengine\ListDocumentsRequest;
use google\appengine\ListDocumentsResponse;
use google\appengine\runtime\ApiProxy;
use google\appengine\runtime\ApplicationError;
use google\appengine\ScorerSpec\Scorer;
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
     * The index namespace
     *
     * @var string
     */
    protected $str_namespace = null;

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
     * Set the index name and optionally a namespace
     *
     * @param string $str_index_name
     * @param null|string $str_namespace
     */
    public function __construct($str_index_name, $str_namespace = null)
    {
        $this->str_index_name = $str_index_name;
        $this->str_namespace = $str_namespace;
    }

    /**
     * Prepare the request parameters
     *
     * Index specs: consistency, mode, name, namespace, source, version
     *
     * @param $obj_request
     * @return object
     */
    private function prepareRequestParams($obj_request)
    {
        $obj_params = $obj_request->mutableParams();
        $obj_spec = $obj_params->mutableIndexSpec()->setName($this->str_index_name);
        if(null !== $this->str_namespace) {
            $obj_spec->setNamespace($this->str_namespace);
        }
        return $obj_params;
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
        $obj_params = $this->prepareRequestParams($obj_request);

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
     * @return object
     * @throws ApplicationError
     * @throws \Exception
     */
    public function search(Query $obj_query)
    {
        $obj_request = new SearchRequest();
        $obj_params = $this->prepareRequestParams($obj_request);

        // Basics
        $obj_params
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

        // Match Scoring
        if(Query::SCORE_REGULAR === $obj_query->getScorer()) {
            $obj_params->mutableScorerSpec()->setScorer(Scorer::MATCH_SCORER)->setLimit($obj_query->getLimit());
        } elseif (Query::SCORE_RESCORING === $obj_query->getScorer()) {
            $obj_params->mutableScorerSpec()->setScorer(Scorer::RESCORING_MATCH_SCORER)->setLimit($obj_query->getLimit());
        }

        // Return Fields
        $arr_return_fields = $obj_query->getReturnFields();
        if(null !== $arr_return_fields && count($arr_return_fields) > 0) {
            $obj_fields = $obj_params->mutableFieldSpec();
            foreach ($arr_return_fields as $str_field) {
                $obj_fields->addName($str_field);
            }
        }

        // Return Expressions
        $arr_return_exps = $obj_query->getReturnExpressions();
        if(null !== $arr_return_exps && count($arr_return_exps) > 0) {
            $obj_fields = $obj_params->mutableFieldSpec();
            foreach ($arr_return_exps as $arr_exp) {
                $obj_fields->addExpression()->setName($arr_exp[0])->setExpression($arr_exp[1]);
            }
        }

        // Facets
        $arr_facets = $obj_query->getFacets();
        if(null !== $arr_facets) {
            if(count($arr_facets) > 0) {
                // We want a specific set of facets in the response
                foreach($arr_facets as $str_facet) {
                    $obj_params->addIncludeFacet()->setName($str_facet);
                }
            } else {
                // We want all facets back...
                $obj_params->setAutoDiscoverFacetCount(100);
            }
        }

        $this->execute('Search', $obj_request, new SearchResponse());
        return $this->processSearchResponse();
    }

    /**
     * Return a single document by ID
     *
     * @param $str_id
     * @return array
     * @throws ApplicationError
     * @throws \Exception
     */
    public function getDocById($str_id)
    {
        $obj_request = new ListDocumentsRequest();
        $obj_params = $this->prepareRequestParams($obj_request);
        $obj_params->setStartDocId($str_id)->setLimit(1);
        $this->execute('ListDocuments', $obj_request, new ListDocumentsResponse());
        $obj_response = $this->processListResponse();

        // Verify that the document is the one we want and if not, empty the response
        // This works around the lack of a "get-by-id" method on the Google Protocol Buffer
        if($obj_response->count > 0) {
            if($str_id !== $obj_response->docs[0]->getId()) {
                $obj_response->count = 0;
                $obj_response->docs = [];
            }
        }
        return $obj_response;
    }

    /**
     * Delete one or more documents by ID
     *
     * @param array $arr_ids
     */
    public function delete(array $arr_ids)
    {
        $obj_request = new DeleteDocumentRequest();
        $obj_params = $this->prepareRequestParams($obj_request);
        foreach($arr_ids as $str_id) {
            $obj_params->addDocId($str_id);
        }
        $this->execute('DeleteDocument', $obj_request, new DeleteDocumentResponse());
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
     * @return object
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
            $obj_doc = $obj_mapper->fromGoogle($obj_result->getDocument(), $obj_result->getExpressionList());
            $obj_response->results[] = (object)[
                'score' => ($obj_result->getScoreSize() > 0 ? $obj_result->getScore(0) : 0),
                'doc' => $obj_doc
            ];
        }

        // Map facets from results
        // @todo Restructure/review response object and moving this code to Mapper
        if($obj_search_response->getFacetResultSize() > 0) {
            $obj_response->facets = []; // create the empty facets placeholder in the response
            /** @var \google\appengine\FacetResult $obj_facet */
            foreach($obj_search_response->getFacetResultList() as $obj_facet) {
                $arr_facet_values = [];
                /** @var \google\appengine\FacetResultValue $obj_facet_value */
                foreach($obj_facet->getValueList() as $obj_facet_value) {
                    $arr_facet_values[] = [
                        'agg' => $obj_facet_value->getName(),
                        'count' => $obj_facet_value->getCount()
                    ];
                }
                $obj_response->facets[$obj_facet->getName()] = $arr_facet_values;
            }
        }

        return $obj_response;
    }

    /**
     * Process a document list response
     *
     * @return object
     */
    private function processListResponse()
    {
        /** @var ListDocumentsResponse $obj_list_response */
        $obj_list_response = $this->obj_last_response;
        $obj_response = (object)[
            'status' => $this->describeStatusCode($obj_list_response->getStatus()->getCode()),
            'count' => $obj_list_response->getDocumentSize(),
            'docs' => []
        ];
        $obj_mapper = new Mapper();
        foreach($obj_list_response->getDocumentList() as $obj_document) {
            $obj_doc = $obj_mapper->fromGoogle($obj_document);
            $obj_response->docs[] = $obj_doc;
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
     * Get the last response message
     *
     * @return ProtocolMessage
     */
    public function getLastResponse()
    {
        return $this->obj_last_response;
    }

    /**
     * Get the last request message
     *
     * @return ProtocolMessage
     */
    public function getLastRequest()
    {
        return $this->obj_last_request;
    }

}