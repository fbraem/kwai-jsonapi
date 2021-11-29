<?php
declare(strict_types=1);

use Kwai\JSONAPI;

it('can resolve an id from a method', function () {
    $person = new #[JSONAPI\Resource(type: 'people', id: 'getId')] class(
        name: 'Jigoro Kano'
    ) {
        public function __construct(
            #[JSONAPI\Attribute]
            private string $name
        ) {
        }

        public function getId(): string
        {
            return uniqid();
        }
    };

    try {
        $jsonapi = JSONAPI\Document::createFromObject($person)->serialize();
    } catch (JSONAPI\Exception $e) {
        $this->fail((string) $e);
    }

    $json = json_decode($jsonapi);

    expect($json)
        ->toHaveProperty('data')
        ->data
        ->toHaveProperty('id')
        ->data->id
        ->toBeString()
    ;
});
