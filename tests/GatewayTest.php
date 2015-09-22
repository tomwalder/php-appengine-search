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
 * Tests for Gateway class
 *
 * @author Tom Walder <tom@docnet.nu>
 */
class GatewayTest extends \google\appengine\testing\ApiProxyTestBase
{

    /**
     * Can we create a Gateway?
     */
    public function testExists()
    {
        $obj_gateway = new \Search\Gateway('some-index');
        $this->assertInstanceOf('\\Search\\Gateway', $obj_gateway);
    }

    /**
     * Basic search test
     *
     * @todo Add assertions for response
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
        $obj_gateway = new \Search\Gateway($str_index);
        $obj_gateway->search(new \Search\Query($str_query));
        $this->apiProxyMock->verify();
    }

    /**
     * Test the delete document function
     *
     * @todo Add assertions for response
     */
    public function testDelete()
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
        $obj_gateway = new \Search\Gateway($str_index);
        $obj_gateway->delete($arr_ids);
        $this->apiProxyMock->verify();
    }

    /**
     * Test get by ID. Also ensure we can access the last request and response objects.
     *
     * @todo Add assertions for response
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
        $obj_gateway = new \Search\Gateway($str_index);
        $obj_gateway->getDocById($str_id);
        $this->apiProxyMock->verify();

        $this->assertInstanceOf('\\google\\appengine\\ListDocumentsRequest', $obj_gateway->getLastRequest());
        $this->assertInstanceOf('\\google\\appengine\\ListDocumentsResponse', $obj_gateway->getLastResponse());
    }

}