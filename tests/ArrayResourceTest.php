<?php

declare(strict_types=1);

use Kwai\JSONAPI;
use Kwai\JSONAPI\Resources\Person;

it('can serialize an array', function () {
    $persons = [
        new Person(
            id: '1',
            name: 'Jigoro Kano',
            age: 77
        ),
        new Person(
            id: '2',
            name: 'Anton Geesink',
            age: 76
        )
    ];

    try {
        $jsonapi = JSONAPI\Document::createFromArray($persons)->serialize();
    } catch (JSONAPI\Exception $e) {
        $this->fail((string) $e);
    }

    $json = json_decode($jsonapi);

    expect($json)
        ->toMatchObject((object) [
            'data' => [
                (object) [
                    'type' => 'people',
                    'id' => '1',
                    'attributes' => (object) [
                        'name' => 'Jigoro Kano',
                        'age' => 77
                    ]
                ],
                (object) [
                    'type' => 'people',
                    'id' => '2',
                    'attributes' => (object) [
                        'name' => 'Anton Geesink',
                        'age' => 76
                    ]
                ],
            ]
        ])
    ;
});
