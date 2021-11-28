<?php

declare(strict_types=1);

use Kwai\JSONAPI;
use Kwai\JSONAPI\Resources\Person;

it('can handle meta data', function () {
    $person = new Person(
        id: '1',
        name: 'Jigoro Kano',
        age: 77
    );

    try {
        $jsonapi =
            JSONAPI\Document::createFromObject($person)
                ->setMeta('count', 1)
                ->serialize();
    } catch (JSONAPI\Exception $e) {
        $this->fail((string) $e);
    }

    $json = json_decode($jsonapi);

    expect($json)
        ->toMatchObject([
            'meta' => (object) [
                'count' => 1
            ],
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
