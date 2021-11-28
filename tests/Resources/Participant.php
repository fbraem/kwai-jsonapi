<?php
/**
 * @package
 * @subpackage
 */
declare(strict_types=1);

namespace Kwai\JSONAPI\Resources;

use Kwai\JSONAPI;

/**
 * Class Participant
 *
 * A sample that contains a relationship with multiple resources.
 */
#[JSONAPI\Resource(type:'athletes')]
class Participant
{
    public function __construct(
        private string $id,
        #[JSONAPI\Attribute]
        private string $name,
        #[JSONAPI\Relationship]
        private Country $country,
        #[JSONAPI\Relationship(name: 'participations')]
        private array $championships
    ) {
    }
}
