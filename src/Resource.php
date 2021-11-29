<?php
declare(strict_types=1);

namespace Kwai\JSONAPI;

/**
 * Resource attribute for a JSONAPI resource
 */
#[\Attribute(\Attribute::TARGET_CLASS)]
class Resource
{
    public function __construct(
        private string $type,
        private null|string|Closure $id = null
    ) {
    }
}
