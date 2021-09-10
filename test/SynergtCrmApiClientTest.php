<?php

use PHPUnit\Framework\TestCase;
use SynergyCrm\ApiClient;
use WoohooLabs\Yang\JsonApi\Request\ResourceObject;
require_once __DIR__ . '/../vendor/autoload.php';

class SynergyCrmApiClientTest extends TestCase
{
    protected static $client = '';
    protected static $createdContactId = 28214;
    protected static $createdCompanyId = 718218;

    protected static $email = 'brekke.adrienne@gmail.com';
    protected static $faker = '';

    # protected  $faker = '';

    public static function setUpBeforeClass()
    {
        self::$client = new ApiClient(
            'http://localhost:3000/api/v1/',
            'e4b9ec90ee3e2ff81240129265bc7cfd640bfc51268bc9d5a545af8dde937942');
        self::$faker = Faker\Factory::create();

       # self::$email = '123carmella.zemlak@morar.biz';

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
        $this->assertTrue(  is_array( $companies->document()->includedResources() ));
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
        self::$createdContactId = (int)$contact->document()->primaryResource()->id();
        # $this->assertIsArray(  $companies->includedResources() );
    }

    public function testCanUpdateContact()
    {
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
        $this->assertTrue(  $contact->hasDocument() );
        $this->assertTrue(  $contact->document()->hasAnyPrimaryResources() );
        $this->assertEquals($firstName,
            $contact->document()->primaryResource()->attribute("first-name"));
        # $this->assertIsArray(  $companies->includedResources() );
    }


    public  function  testCanCreateCompany()
    {
        $companyObject = new ResourceObject("companies", '');
        $companyObject->setAttributes(array(
            "name" => self::$faker->company()
        ));
        $relation = new \WoohooLabs\Yang\JsonApi\Request\ToManyRelationship();
        $relation->addResourceIdentifier("contacts", self::$createdContactId);
        $companyObject->setToManyRelationship("contacts", $relation);

        $result = self::$client->createCompany($companyObject);

        self::$createdCompanyId = $result->document()->primaryResource()->id();
        $this->assertTrue($result->isSuccessful());
        $this->assertTrue($result->hasDocument());
    }

    public  function  testCanUpdateCompany()
    {
        $companyObject = new ResourceObject("companies", self::$createdCompanyId);
        $companyObject->setAttributes(array(
            "name" => self::$faker->company()
        ));
        $relation = new \WoohooLabs\Yang\JsonApi\Request\ToManyRelationship();
        $relation->addResourceIdentifier("contacts", self::$createdContactId);
        $companyObject->setToManyRelationship("contacts", $relation);

        $result = self::$client->updateCompany($companyObject);

        $this->assertTrue($result->isSuccessful());
        $this->assertTrue($result->hasDocument());
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

        $result = self::$client->createDeal($ro);
        $this->assertTrue(  $result->hasDocument() );
        $this->assertTrue(  $result->isSuccessful() );
    }

    public function testCanGetContactsWithFilter()
    {
        echo "using email: ".self::$email.", id: ".self::$createdContactId."\n";
        $response = self::$client->getContacts(array('email' => self::$email), ['companies','deals']);
        $document = $response->document();
        $primaryResources = $document->primaryResources();
        $this->assertTrue(  is_array(  $primaryResources ));
        $this->assertTrue( isset($primaryResources[0]) );

        if ($response && $response->isSuccessful() && isset($primaryResources[0])) {
            $customer = $primaryResources[0];
            echo "got email:   ".$customer->attribute('email').", id: ".$customer->id()."\n";
            $this->assertEquals(self::$email, $customer->attribute('email') );
            $this->assertEquals(self::$createdContactId, $customer->id() );
        }
        $included = $response->document()->includedResources();
        $related_companies = array_filter($included, function ($r) { return $r->type() == "companies"; });

        $this->assertTrue(  is_array($included));
        $this->assertEquals(self::$createdCompanyId, $related_companies[0]->id());

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

