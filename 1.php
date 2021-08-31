<?php

use GuzzleHttp\Psr7\Request;
use WoohooLabs\Yang\JsonApi\Request\JsonApiRequestBuilder;

// Instantiate an empty PSR-7 request, note that the default HTTP method must be provided
$request = new Request('GET', '');

// Instantiate the request builder
$requestBuilder = new JsonApiRequestBuilder($request);

// Setup the request with general properties
$requestBuilder
    ->setMethod("GET")
    ->setUri("http://localhost/api/v1/companies")
    ->setHeader('Bearer', '014531a2371c7fe63ea573d909f78fdcd4c9334961d1712611e107454067f49f')
    ->setHeader("Accept-Charset", "utf-8");

// Setup the request with JSON:API specific properties
$requestBuilder
    ->setJsonApiFields(                                      // To define sparse fieldset
        [
            "users" => ["first_name", "last_name"],
            "address" => ["country", "city", "postal_code"]
        ]
    )
    ->setJsonApiIncludes(                                    // To include related resources
        ["address", "friends"]
    )
    ->setJsonApiIncludes(                                    // Or you can pass a string instead
        "address,friends"
    )
    ->setJsonApiSort(                                        // To sort resource collections
        ["last_name", "first_name"]
    )
    ->setJsonApiPage(                                        // To paginate the primary data
        ["number" => 1, "size" => 100]
    )
    ->setJsonApiFilter(                                      // To filter the primary data
        ["first_name" => "John"]
    )
    ->addJsonApiAppliedProfile(                              // To add a profile to the request (JSON:API 1.1 feature)
        ["https://example.com/profiles/last-modified"]
    )
    ->addJsonApiRequestedProfile(                            // To request the server to apply a profile (JSON:API 1.1 feature)
        ["https://example.com/profiles/last-modified"]
    )
    ->addJsonApiRequiredProfile(                             // To require the server to apply a profile (JSON:API 1.1 feature)
        ["https://example.com/profiles/last-modified"]
    );

// Setup the request body
$requestBuilder
    ->setJsonApiBody(                                        // You can pass the content as a JSON string
        '{
           "data": [
             { "type": "user", "id": "1" },
             { "type": "user", "id": "2" }
           ]
         }'
    )
    ->setJsonApiBody(                                        // or you can pass it as an array
        [
            "data" => [
                ["type" => "user", "id" => 1],
                ["type" => "user", "id" => 2],
            ],
        ]
    )
    ->setJsonApiBody(                                        // or as a ResourceObject instance
        new ResourceObject("user", 1)
    );

// Get the composed request
$request = $requestBuilder->getRequest();