<?php
declare(strict_types=1);

namespace Kwai\JSONAPI\Resources;

use Kwai\JSONAPI;

/**
 * Class Country
 */
#[JSONAPI\Resource(type: 'countries')]
class Country
{
    public function __construct(
        private string $id,
        #[JSONAPI\Attribute]
        private string $code
    ) {
    }
}
