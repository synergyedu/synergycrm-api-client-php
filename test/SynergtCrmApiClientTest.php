<?php

use PHPUnit\Framework\TestCase;
use SynergyCrm\ApiClient;
use WoohooLabs\Yang\JsonApi\Request\ResourceObject;
use WoohooLabs\Yang\JsonApi\Request\RelationshipInterface;
require_once __DIR__ . '/../vendor/autoload.php';

class SynergyCrmApiClientTest extends TestCase
{
    protected $client = '';
    public function setUp(): void
    {
            $this->client = new ApiClient(
                'http://localhost:3000/api/v1/',
                'e4b9ec90ee3e2ff81240129265bc7cfd640bfc51268bc9d5a545af8dde937942');

		    $this->assertInstanceOf( ApiClient::class, $this->client );
    }

    public function testCanGetCompanies(): void
    {
        $companies = $this->client->getCompanies();
        # var_dump( $companies );
        $this->assertTrue(  $companies->hasDocument() );
        $this->assertTrue(  $companies->document()->hasAnyPrimaryResources() );
        $this->assertIsArray(  $companies->document()->includedResources() );
    }

    public function testCanCreateContact(): void
    {
        $ro = new ResourceObject("contacts",'');
        $ro->setAttributes( array(
                "first-name" => "bar",
                "last-name" => "foo"
                ));

        $contact = $this->client->createContact($ro);
        $this->assertTrue(  $contact->hasDocument() );
        $this->assertTrue(  $contact->document()->hasAnyPrimaryResources() );
        # $this->assertIsArray(  $companies->includedResources() );
    }

    public  function  testCanCreateDeal(): void
    {
        $ro = new ResourceObject("deals", '');
        $ro->setAttributes( array(
            "name" => "Заказ №123",
            "amount" => 123.0
        ) );

        $relation = new \WoohooLabs\Yang\JsonApi\Request\ToOneRelationship( "deal-stage-categories", 544  );
        $ro->setRelationship("stage-category", $relation );

        $deal = $this->client->createDeal($ro);
        $this->assertTrue(  $deal->hasDocument() );
        $this->assertTrue(  $deal->isSuccessful() );
    }


    public  function testCanPost(): void
    {
        $deal = $this->client->sendPostRequest("deals", '{
  "data": {
    "type": "deals",
    "attributes": {
      "name": "Заказ №123",
      "amount": 123.0
    },
    "relationships": {
      "stage-category": {
        "data": {
          "type": "deal-stage-categories",
          "id": "544"
        }
      }
    }
  }
}');
        $this->assertTrue(  $deal->isSuccessful() );
    }
}

