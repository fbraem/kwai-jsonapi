<?php
declare(strict_types=1);

namespace Kwai\JSONAPI\Resources;

use Kwai\JSONAPI;

/**
 * Class Athlete
 *
 * A sample with a relationship
 */
#[JSONAPI\Resource(type:'athletes')]
class Athlete
{
    public function __construct(
        private string $id,
        #[JSONAPI\Attribute]
        private string $name,
        #[JSONAPI\Relationship]
        private ?Country $country = null
    ) {
    }
}
