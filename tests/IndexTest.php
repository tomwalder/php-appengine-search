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
 * Tests for Index class
 *
 * @author Tom Walder <tom@docnet.nu>
 */
class IndexTest extends \google\appengine\testing\ApiProxyTestBase
{

    /**
     * Can we create an Index?
     */
    public function testExists()
    {
        $obj_index = new \Search\Index('some-index');
        $this->assertInstanceOf('\\Search\\Index', $obj_index);
    }

    /**
     * Test put failure
     *
     * @expectedException        InvalidArgumentException
     * @expectedExceptionMessage Parameter must be one or more \Search\Document objects
     */
    public function testPutTypeFailure()
    {
        $obj_index = new \Search\Index('test');
        $obj_index->put('error-string');
    }

    /**
     * Basic search test
     */
    public function testBasicSearch()
    {
        $str_index = 'test-index';
        $str_query = 'phrase';

        $obj_request = new \google\appengine\SearchRequest();
        $obj_request->mutableParams()
            ->setQuery($str_query)
            ->setLimit(20)
            ->setOffset(0)
            ->mutableIndexSpec()->setName($str_index);

        $this->apiProxyMock->expectCall('search', 'Search', $obj_request, new \google\appengine\SearchResponse());
        $obj_index = new \Search\Index($str_index);
        $obj_index->search($str_query);
        $this->apiProxyMock->verify();
    }

    /**
     * Basic search test
     */
    public function testQuerySearch()
    {
        $str_index = 'test-index';
        $str_query = 'phrase';

        $obj_request = new \google\appengine\SearchRequest();
        $obj_request->mutableParams()
            ->setQuery($str_query)
            ->setLimit(20)
            ->setOffset(0)
            ->mutableIndexSpec()->setName($str_index);

        $this->apiProxyMock->expectCall('search', 'Search', $obj_request, new \google\appengine\SearchResponse());
        $obj_index = new \Search\Index($str_index);
        $obj_index->search(new \Search\Query($str_query));
        $this->apiProxyMock->verify();
    }

    /**
     * Test get by ID. Also ensure we can access the last request and response objects.
     */
    public function testGetById()
    {
        $str_index = 'test-index';
        $str_id = 'abc123def456';

        $obj_request = new \google\appengine\ListDocumentsRequest();
        $obj_params = $obj_request->mutableParams();
        $obj_params->mutableIndexSpec()->setName($str_index);
        $obj_params->setStartDocId($str_id)->setLimit(1);
        $obj_response = new \google\appengine\ListDocumentsResponse();

        $this->apiProxyMock->expectCall('search', 'ListDocuments', $obj_request, $obj_response);
        $obj_index = new \Search\Index($str_index);
        $obj_index->get($str_id);
        $this->apiProxyMock->verify();

        $arr_debug = $obj_index->debug();
        $this->assertInstanceOf('\\google\\appengine\\ListDocumentsRequest', $arr_debug[0]);
        $this->assertInstanceOf('\\google\\appengine\\ListDocumentsResponse', $arr_debug[1]);
    }

    /**
     * Test the delete document function
     */
    public function testDeleteArrayIdStrings()
    {
        $str_index = 'test-index';
        $arr_ids = ['123456789', 'abc123'];

        $obj_request = new \google\appengine\DeleteDocumentRequest();
        $obj_params = $obj_request->mutableParams();
        $obj_params->mutableIndexSpec()->setName($str_index);
        foreach($arr_ids as $str_id) {
            $obj_params->addDocId($str_id);
        }

        $this->apiProxyMock->expectCall('search', 'DeleteDocument', $obj_request, new \google\appengine\DeleteDocumentResponse());
        $obj_index = new \Search\Index($str_index);
        $obj_index->delete($arr_ids);
        $this->apiProxyMock->verify();
    }

    /**
     * Test the delete document function with a variety of inputs
     */
    public function testDeleteMulti()
    {
        $str_index = 'test-index';
        $str_id = '123456789';

        $obj_request = new \google\appengine\DeleteDocumentRequest();
        $obj_params = $obj_request->mutableParams();
        $obj_params->mutableIndexSpec()->setName($str_index);
        $obj_params->addDocId($str_id);

        $this->apiProxyMock->expectCall('search', 'DeleteDocument', $obj_request, new \google\appengine\DeleteDocumentResponse());
        $obj_index = new \Search\Index($str_index);
        $obj_index->delete($str_id);
        $this->apiProxyMock->verify();

        $this->apiProxyMock->expectCall('search', 'DeleteDocument', $obj_request, new \google\appengine\DeleteDocumentResponse());
        $obj_doc = new \Search\Document();
        $obj_doc->setId($str_id);
        $obj_index->delete($obj_doc);
        $this->apiProxyMock->verify();

        $this->apiProxyMock->expectCall('search', 'DeleteDocument', $obj_request, new \google\appengine\DeleteDocumentResponse());
        $obj_doc = new \Search\Document();
        $obj_doc->setId($str_id);
        $obj_index->delete([$obj_doc]);
        $this->apiProxyMock->verify();
    }

    /**
     * Test the delete document function failure
     *
     * @expectedException        InvalidArgumentException
     * @expectedExceptionMessage Parameter must be one or more \Search\Document objects
     */
    public function testDeleteFailure()
    {
        $obj_index = new \Search\Index('test');
        $obj_index->delete(123);
    }

    /**
     * Test the delete document function failure
     *
     * @expectedException        InvalidArgumentException
     * @expectedExceptionMessage Parameter must be one or more \Search\Document objects
     */
    public function testDeleteFailureArray()
    {
        $obj_index = new \Search\Index('test');
        $obj_index->delete(['abc', 123]);
    }

}