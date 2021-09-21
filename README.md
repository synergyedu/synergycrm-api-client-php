## Synergy API Client

PHP JSON:API Client for SynergyCRM

### Installation

    composer require synergyedu/synergycrm-api-client-php:dev-main

### Usage

    use SynergyCrm\ApiClient;
    use WoohooLabs\Yang\JsonApi\Request\ResourceObject;

    $api_token = 'e4b9ec90ee3e2ff81240129265bc7cfd640bfc51268bc9d5a545af8dde937942';
    $client = new ApiClient('https://app.syncrm.ru/api/v1/', $api_token);

    # create contact with error handling

    $contactObject = new ResourceObject("contacts",'');
    $contactObject->setAttributes( array(
        "first-name" => "Ivan",
        "last-name" => "Ivanov",
        'email' => "ivan.ivanov@example.com"
    ));


    try {
        $response = $client->createContact($contactObject);
    } catch (Http\Client\Exception\NetworkException $e) {
        echo($e->getMessage()."\n");
    }


    if ($response->isSuccessful()) {
        $contact = $response->document()->primaryResource();
        $createdContactId = $contact->id();
        echo $createdContactId;
    } else {
        echo(sprintf("Request failed: %s\n", $response->getReasonPhrase()));
        if ($response->hasDocument() && $response->document()->hasErrors()) {
            foreach ($response->document()->errors() as $error) {
              echo(sprintf("Error: %s: %s\n", $error->title(), $error->detail()));
            }
        }
    }
    
    # update contact using JSON

    $response = $client->sendPatchRequest("contacts", '{
      "data": {
        "type": "contacts",
        "id": '. $createdContactId .',
        "attributes": {
          "last-name": "Petrov"
        }
      }
    }');

    
    # delete contact specifying id as part of method
    $response = $client->sendDeleteRequest("contacts/".$createdContactId);

    # delete contact using resource object
    $response = $client->sendDeleteRequest("contacts", $contact);
