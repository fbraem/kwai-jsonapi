<?php

declare(strict_types=1);

use Kwai\JSONAPI;
use Kwai\JSONAPI\Resources\Athlete;
use Kwai\JSONAPI\Resources\Country;

it('can serialize a resource with a relationship', function () {
    $belgium = new Country(
        id: '1',
        code: 'BEL'
    );
    $athlete = new Athlete(
        id: '1',
        name: 'Ingrid Berghmans',
        country: $belgium
    );

    try {
        $jsonapi = JSONAPI\Document::createFromObject($athlete)->serialize();
    } catch (JSONAPI\Exception $e) {
        $this->fail((string) $e);
    }

    $json = json_decode($jsonapi);

    expect($json)
        ->toMatchObject([
            'data' => (object) [
                'type' => 'athletes',
                'id' => '1',
                'attributes' => (object) [
                    'name' => 'Ingrid Berghmans'
                ],
                'relationships' => (object) [
                    'country' => (object) [
                        'data' => (object) [
                            'type' => 'countries',
                            'id' => '1'
                        ]
                    ]
                ]
            ],
            'included' => [
                (object)[
                    'type' => 'countries',
                    'id' => '1',
                    'attributes' => (object)[
                        'code' => 'BEL'
                    ]
                ]
            ]
        ])
    ;
});

it('can serialize a resource with a missing relationship', function () {
    $athlete = new Athlete(
        id: '1',
        name: 'Ingrid Berghmans',
    );

    try {
        $jsonapi = JSONAPI\Document::createFromObject($athlete)->serialize();
    } catch (JSONAPI\Exception $e) {
        $this->fail((string) $e);
    }

    $json = json_decode($jsonapi);

    expect($json)
        ->toMatchObject([
            'data' => (object) [
                'type' => 'athletes',
                'id' => '1',
                'attributes' => (object) [
                    'name' => 'Ingrid Berghmans'
                ]
            ]
        ])
    ;
});
