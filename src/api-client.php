<?php
namespace SynergyCrm;

use GuzzleHttp\Psr7\Request;
use mysql_xdevapi\Exception;
use parallel\Sync\Error;
use WoohooLabs\Yang\JsonApi\Request\JsonApiRequestBuilder;
use WoohooLabs\Yang\JsonApi\Client\JsonApiClient;
use Http\Adapter\Guzzle6\Client;

class ApiClient
{
    public $token = '';
    public $url = '';
    public $client = null;
    function __construct($url="http://localhost:3000/api/v1/",$token='') {
        $this->url = $url;
        $this->token = $token;
        // Instantiate the Guzzle HTTP Client
        $guzzleClient = Client::createWithConfig([]);

        // Instantiate the syncronous JSON:API Client
        $this->client = new JsonApiClient($guzzleClient);


    }

    public static function world()
    {
        return 'Hello World, Composer!';
    }

    public  function getRequest($method,$filter='')
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
        return $requestBuilder->getRequest();
    }

    public  function sendPostRequest($api_method, $body = '') {
        return $this->sendAnyRequest("POST", $api_method, $body);
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

    public  function createDeal($data)
    {
        return $this->sendPostRequest("deals", $data);
    }

    public function getCompanies()
    {
        $request = $this->getRequest("companies");
        $response = $this->client->sendRequest($request);
        return $this->processedResponse($response);
    }


    public function getContacts($filter='')
    {
        $request = $this->getRequest("contacts",$filter);
        $response = $this->client->sendRequest($request);
        return $this->processedResponse($response);
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
    public function buildAnyRequest(string $http_method, $api_method, $body)
    {
        $requestBuilder = $this->buildRequest();

        $id = '';
        if (is_string($body)) {
            $decoded = json_decode($body,true);
            if (is_array($decoded) && array_key_exists('id',$decoded['data'])) {
                $id = "/" . $decoded['data']['id'] . "/";
            }
        } elseif (is_a($body,"ResourceObject")) {
            $id = "/" . $body->id() . "/";
        }

        // Setup the request with general properties
        $requestBuilder
            ->setMethod($http_method)
            ->setUri($this->url . $api_method . $id)
            ->setHeader('Authorization', 'Bearer ' . $this->token)
            ->setJsonApiBody( // string, array or as a ResourceObject instance
                $body
            );

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
}