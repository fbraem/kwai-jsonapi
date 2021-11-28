<?php
declare(strict_types=1);

namespace Kwai\JSONAPI;

/**
 * Class Relationship
 */
#[\Attribute(\Attribute::TARGET_METHOD | \Attribute::TARGET_PROPERTY)]
class Relationship
{
    public function __construct(
        private ?string $name = null
    ) {
    }
}
