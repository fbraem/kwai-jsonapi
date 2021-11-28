<?php
declare(strict_types=1);

namespace Kwai\JSONAPI\Resources;

use Kwai\JSONAPI;

/**
 * Class Person
 */
#[JSONAPI\Resource(type:'people')]
class Person
{
    public function __construct(
        private string $id,
        #[JSONAPI\Attribute]
        private string $name,
        #[JSONAPI\Attribute]
        private int $age
    ) {
    }
}
