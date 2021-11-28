<?php
declare(strict_types=1);

use Kwai\JSONAPI;
use Kwai\JSONAPI\Resources\Championship;
use Kwai\JSONAPI\Resources\Person;

it('can serialize a simple resource', function () {
    $person = new Person(
        id: '1',
        name: 'Jigoro Kano',
        age: 77
    );

    try {
        $jsonapi = JSONAPI\Document::createFromObject($person)->serialize();
    } catch (JSONAPI\Exception $e) {
        $this->fail((string) $e);
    }

    $json = json_decode($jsonapi);

    expect($json)
        ->toMatchObject([
            'data' => (object) [
                'type' => 'people',
                'id' => '1',
                'attributes' => (object) [
                    'name' => 'Jigoro Kano',
                    'age' => 77
                ]
            ]
        ])
    ;
});

it('can serialize a resource with a method for attribute', function () {
   $championship = new Championship(
       id: '1',
       year: 1980,
       weight: '+72',
       result: 3
   );

    try {
        $jsonapi = JSONAPI\Document::createFromObject($championship)->serialize();
    } catch (JSONAPI\Exception $e) {
        $this->fail((string) $e);
    }

    $json = json_decode($jsonapi);

    expect($json)
        ->toMatchObject([
            'data' => (object) [
                'type' => 'championships',
                'id' => '1',
                'attributes' => (object) [
                    'year' => 1980,
                    'weight' => '+72',
                    'result' => 3
                ]
            ]
        ])
    ;
});
