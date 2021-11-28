<?php
/**
 * @package
 * @subpackage
 */
declare(strict_types=1);

namespace Kwai\JSONAPI\Resources;

use Kwai\JSONAPI;

/**
 * Class Championship
 *
 * A sample with a resource and a method that is used to retrieve an attribute.
 */
#[JSONAPI\Resource(type:'championships')]
class Championship
{
    public function __construct(
        private string $id,
        #[JSONAPI\Attribute]
        private int $year,
        #[JSONAPI\Attribute]
        private string $weight,
        private int $result
    ) {
    }

    #[JSONAPI\Attribute(name: 'result')]
    public function getResult(): int
    {
        return $this->result;
    }
}
