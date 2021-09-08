<?php

use PHPUnit\Framework\TestCase;
use SynergyCrm\ApiClient;
use WoohooLabs\Yang\JsonApi\Request\ResourceObject;
use WoohooLabs\Yang\JsonApi\Request\RelationshipInterface;
require_once __DIR__ . '/../vendor/autoload.php';

class SynergyCrmApiClientTest extends TestCase
{
    protected static $client = '';
    protected static $createdContactId = 0;
    protected static $email = '';
    protected static $faker = '';

    # protected  $faker = '';

    public static function setUpBeforeClass(): void
    {
        self::$client = new ApiClient(
            'http://localhost:3000/api/v1/',
            'e4b9ec90ee3e2ff81240129265bc7cfd640bfc51268bc9d5a545af8dde937942');
        self::$faker = Faker\Factory::create();

        self::$email = '123carmella.zemlak@morar.biz';

        #self::$email = uniqid().'@example.com';
        # self::$email = '123carmella.zemlak@morar.biz'; # self::$faker->email(); #  uniqid().'@example.com';

        # $this->assertInstanceOf( ApiClient::class, self::$client );
    }

    public function testCanGetCompanies()
    {
        $companies = self::$client->getCompanies();
        # var_dump( $companies );
        $this->assertTrue(  $companies->hasDocument() );
        $this->assertTrue(  $companies->document()->hasAnyPrimaryResources() );
        $this->assertIsArray(  $companies->document()->includedResources() );
    }


    public function testCanCreateContact()
    {
        self::$email = self::$faker->email; #  uniqid().'@example.com';
        $contactObject = new ResourceObject("contacts",'');
        $contactObject->setAttributes( array(
            "first-name" => self::$faker->firstName(),
            "last-name" => self::$faker->lastName(),
            'email' => self::$email
        ));

        $contact = self::$client->createContact($contactObject);
        $this->assertTrue(  $contact->hasDocument() );
        $this->assertTrue(  $contact->document()->hasAnyPrimaryResources() );
        self::$createdContactId = $contact->document()->primaryResource()->id();
        # $this->assertIsArray(  $companies->includedResources() );
    }

    public function testCanUpdateContact()
    {
        self::$email = self::$faker->email; #  uniqid().'@example.com';
        self::$createdContactId |= 28185;
        $firstName = "Updated" . self::$faker->firstName;
        if (true) {
            $ro = new ResourceObject("contacts", self::$createdContactId);
            $ro->setAttributes(array(
                "first-name" => $firstName,
            ));
        } else {

            $ro = '{
            "data":{
               "type":"contacts",
               "id": "'.self::$createdContactId.'",
               "attributes": {
                 "first-name": "' . $firstName . '"
                }
              }
            }';
        }
        $contact = self::$client->updateContact($ro);
        $this->assertTrue(  $contact->isSuccessful() );

        $this->assertTrue(  $contact->document()->hasAnyPrimaryResources() );
        $this->assertEquals($firstName,
            self::$createdContactId = $contact->document()->primaryResource()->attribute("first-name"));
        # $this->assertIsArray(  $companies->includedResources() );
    }

    public function testCanGetContactsWithFilter()
    {

        echo "using email: ".self::$email;
        $response = self::$client->getContacts(array('email' => self::$email));
        $document = $response->document()->primaryResources();
        $this->assertIsArray(  $document );
        $this->assertTrue( isset($document[0]) );

        if ($response && $response->isSuccessful() && isset($document[0])) {
            $customer = $document[0];
            $this->assertEquals(self::$email, $customer->attribute('email') );
        }

    }

    public  function  testCanCreateDeal()
    {
        $ro = new ResourceObject("deals", '');
        $ro->setAttributes( array(
            "name" => "Заказ №".self::$faker->randomNumber(),
            "amount" => self::$faker->randomFloat()
        ) );

        $relation = new \WoohooLabs\Yang\JsonApi\Request\ToOneRelationship( "deal-stage-categories", 544  );
        $ro->setRelationship("stage-category", $relation );

        $deal = self::$client->createDeal($ro);
        $this->assertTrue(  $deal->hasDocument() );
        $this->assertTrue(  $deal->isSuccessful() );
    }


    public  function testCanPost()
    {
        $deal = self::$client->sendPostRequest("deals", '{
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

