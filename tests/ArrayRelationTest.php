<?php

declare(strict_types=1);

use Kwai\JSONAPI;
use Kwai\JSONAPI\Resources\Championship;
use Kwai\JSONAPI\Resources\Country;
use Kwai\JSONAPI\Resources\Participant;

it('can serialize a resource with an array of relationships', function () {
    $belgium = new Country(
        id: '1',
        code: 'BEL'
    );

    $championships = [
        new Championship(
            id: '1', year: 1980, weight: '+72', result: 3
        ),
        new Championship(
            id: '2', year: 1980, weight: 'open', result: 1
        ),
        new Championship(
            id: '3', year: 1982, weight: '-72', result: 2
        )
    ];

    $particant = new Participant(
        id: '1',
        name: 'Ingrid Berghmans',
        country: $belgium,
        championships: $championships
    );

    try {
        $jsonapi = JSONAPI\Document::createFromObject($particant)->serialize();
    } catch (JSONAPI\Exception $e) {
        $this->fail((string) $e);
    }

    $json = json_decode($jsonapi);

    expect($json)->toMatchObject([
        "data" => (object) [
            "type" => "athletes",
            "id" => "1",
            "attributes" => (object) [
                "name" => "Ingrid Berghmans",
            ],
            "relationships" => (object) [
                "country" => (object) [
                    "data" => (object) [
                        "type" => "countries",
                        "id" => "1"
                    ]
                ],
                "participations" => (object) [
                    "data" => [
                        (object) [
                            "type" => "championships",
                            "id" => "1"
                        ],
                        (object) [
                            "type" => "championships",
                            "id" => "2"
                        ],
                        (object) [
                            "type" => "championships",
                            "id" => "3"
                        ]
                    ]
                ]
            ]
        ],
        "included" => [
            (object) [
                "type" => "countries",
                "id" => "1",
                "attributes" => (object) [
                    "code" => "BEL",
                ]
            ],
            (object) [
                "type" => "championships",
                "id" => "1",
                "attributes" => (object) [
                    "year" => 1980,
                    "weight" => "+72",
                    "result" => 3
                ]
            ],
            (object) [
                "type" => "championships",
                "id" => "2",
                "attributes" => (object) [
                    "year" => 1980,
                    "weight" => "open",
                    "result" => 1
                ]
            ],
            (object) [
                "type" => "championships",
                "id" => "3",
                "attributes" => (object) [
                    "year" => 1982,
                    "weight" => "-72",
                    "result" => 2
                ]
            ]
        ]
    ]);
});

it('can serialize a resource with an empty array of relationships', function () {
    $belgium = new Country(
        id: '1',
        code: 'BEL'
    );

    $particant = new Participant(
        id: '1',
        name: 'Ingrid Berghmans',
        country: $belgium,
        championships: []
    );

    try {
        $jsonapi = JSONAPI\Document::createFromObject($particant)->serialize();
    } catch (JSONAPI\Exception $e) {
        $this->fail((string) $e);
    }

    $json = json_decode($jsonapi);

    expect($json)->toMatchObject([
        "data" => (object) [
            "type" => "athletes",
            "id" => "1",
            "attributes" => (object) [
                "name" => "Ingrid Berghmans",
            ],
            "relationships" => (object) [
                "country" => (object) [
                    "data" => (object) [
                        "type" => "countries",
                        "id" => "1"
                    ]
                ],
                "participations" => (object) [
                    "data" => []
                ]
            ]
        ],
        "included" => [
            (object) [
                "type" => "countries",
                "id" => "1",
                "attributes" => (object) [
                    "code" => "BEL",
                ]
            ]
        ]
    ]);
});
