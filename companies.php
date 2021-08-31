<?php

require_once __DIR__ . '/vendor/autoload.php';

use GuzzleHttp\Psr7\Request;
use WoohooLabs\Yang\JsonApi\Request\JsonApiRequestBuilder;
use WoohooLabs\Yang\JsonApi\Client\JsonApiClient;
use Http\Adapter\Guzzle6\Client;

// Instantiate an empty PSR-7 request, note that the default HTTP method must be provided
$request = new Request('GET', '');

// Instantiate the request builder
$requestBuilder = new JsonApiRequestBuilder($request);

// Setup the request with general properties
$requestBuilder
    ->setMethod("GET")
    ->setUri("http://localhost:3000/api/v1/companies")
    ->setHeader('Authorization', 'Bearer e4b9ec90ee3e2ff81240129265bc7cfd640bfc51268bc9d5a545af8dde937942')
    ->setHeader("Accept-Charset", "utf-8");

$request = $requestBuilder->getRequest();

// Instantiate the Guzzle HTTP Client
$guzzleClient = Client::createWithConfig([]);

// Instantiate the syncronous JSON:API Client
$client = new JsonApiClient($guzzleClient);

// Send the request syncronously to retrieve the response
$response = $client->sendRequest($request);

// Checks if the response doesn't contain any errors
if ($isSuccessful = $response->isSuccessful()) {

// Checks if the response doesn't contain any errors, and has the status codes listed below
$isSuccessful = $response->isSuccessful([200, 202]);

// The same as the isSuccessful() method, but also ensures the response contains a document
$isSuccessfulDocument = $response->isSuccessfulDocument();

// Checks if the response contains a JSON:API document
$hasDocument = $response->hasDocument();

// Retrieves and deserializes the JSON:API document in the response body
$document = $response->document();

var_dump($document);
} else {
    echo "unsuccessfull";
    var_dump($response);
}