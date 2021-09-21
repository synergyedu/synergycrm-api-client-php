<?php
namespace SynergyCrm;

use GuzzleHttp\Psr7\Request;
use WoohooLabs\Yang\JsonApi\Request\JsonApiRequestBuilder;
use Http\Adapter\Guzzle6\Client;
use WoohooLabs\Yang\JsonApi\JsonApiClient;


class ApiClient
{
    public $token = '';
    public $url = '';
    public $client = null;
    function __construct($url,$token) {
        $this->url = $url;
        $this->token = $token;
        // Instantiate the Guzzle HTTP Client
        $guzzleClient = Client::createWithConfig([]);

        // Instantiate the syncronous JSON:API Client
        $this->client = new JsonApiClient($guzzleClient);


    }


    public function sendGetRequest($api_method, $id = '', $filter='',$includes='') {
        $query = $api_method . $this->extractId($id);
        $request = $this->getRequest($query,$filter,$includes);
        $response = $this->client->sendRequest($request);
        return $this->processedResponse($response);
    }

    public  function sendPostRequest($api_method, $body = '') {
        return $this->sendAnyRequest("POST", $api_method, $body);
    }

    public  function sendDeleteRequest($api_method, $body = '') {
        return $this->sendAnyRequest("DELETE", $api_method, $body);
    }

    public  function sendPatchRequest($api_method, $body = '') {
        return $this->sendAnyRequest("PATCH", $api_method, $body);
    }

    public  function buildPostRequest($api_method, $body = '')
    {
        return $this->buildAnyRequest("POST", $api_method, $body);
    }

    public  function  createContact($data)
    {
        return $this->sendPostRequest("contacts", $data);
    }

    public  function  updateContact($data)
    {
        return $this->sendAnyRequest("PATCH", "contacts", $data);
    }

    public  function  updateCompany($data)
    {
        return $this->sendAnyRequest("PATCH", "companies", $data);
    }

    public  function createDeal($data)
    {
        return $this->sendPostRequest("deals", $data);
    }

    public  function createCompany($data)
    {
        return $this->sendPostRequest("companies", $data);
    }

    public function getCompanies($filter='',$includes='')
    {
        return $this->sendGetRequest("companies", '', $filter, $includes);
    }

    public function getContacts($filter='',$includes='')
    {
        return $this->sendGetRequest("contacts", '', $filter, $includes);
    }

    /**
     * @param \WoohooLabs\Yang\JsonApi\Response\JsonApiResponse $response
     * @return \WoohooLabs\Yang\JsonApi\Response\JsonApiResponse|\WoohooLabs\Yang\JsonApi\Schema\Document
     */
    public function processedResponse(\WoohooLabs\Yang\JsonApi\Response\JsonApiResponse $response)
    {
        return $response;
    }

    /**
     * @return JsonApiRequestBuilder
     */
    public function buildRequest($method='GET')
    {
        // Instantiate an empty PSR-7 request, note that the default HTTP method must be provided
        $request = new Request($method, '');

        // Instantiate the request builder
        $requestBuilder = new JsonApiRequestBuilder($request);
        return $requestBuilder;
    }

    /**
     * @param string $http_method
     * @param $api_method
     * @param $body
     * @return \Psr\Http\Message\RequestInterface
     */
    public function buildAnyRequest($http_method, $api_method, $body)
    {
        $requestBuilder = $this->buildRequest();

        $id = $this->extractId($body);

        // Setup the request with general properties
        $requestBuilder
            ->setMethod($http_method)
            ->setUri($this->url . $api_method . $id)
            ->setHeader('Authorization', 'Bearer ' . $this->token);

        if ($http_method != "DELETE") $requestBuilder->setJsonApiBody($body);

        return $requestBuilder->getRequest();
    }

    /**
     * @param string $http_method
     * @param $api_method
     * @param $body
     * @return \WoohooLabs\Yang\JsonApi\Response\JsonApiResponse|\WoohooLabs\Yang\JsonApi\Schema\Document
     */
    public function sendAnyRequest($http_method, $api_method, $body)
    {
        $request = $this->buildAnyRequest($http_method, $api_method, $body);
        $response = $this->client->sendRequest($request);
        return $this->processedResponse($response);
    }

    public  function getRequest($method,$filter='',$includes='')
    {
        // Instantiate an empty PSR-7 request, note that the default HTTP method must be provided
        $requestBuilder = $this->buildRequest();

        // Setup the request with general properties
        $requestBuilder
            ->setMethod("GET")
            ->setUri($this->url.$method)
            ->setHeader("Content-Type", "application/vnd.api+json")
            ->setHeader('Authorization', 'Bearer ' . $this->token);

        if ($filter != '') $requestBuilder->setJsonApiFilter($filter);
        if ($includes != '') $requestBuilder->setJsonApiIncludes($includes);

        return $requestBuilder->getRequest();
    }

    /**
     * @param $body
     * @return string
     */
    public function extractId($body)
    {
        $id = '';
        if (is_numeric($body)) {
            $id = sprintf("/%d/", (int)$body);
        } else if (is_string($body)) {
            try {
                $decoded = json_decode($body, true);
                if (is_array($decoded) && array_key_exists('id', $decoded['data'])) {
                    $id = "/" . $decoded['data']['id'];
                }
            } catch (\Exception $e) {
            }
        } elseif (method_exists($body, "toArray")) {
            $resource = $body->toArray();
            if (!empty($resource["data"]["id"])) $id = "/" . $resource["data"]["id"];
        } elseif (method_exists($body, "id")) {
            $id = "/" . $body->id();
        }
        return $id;
    }

}