<?php
declare(strict_types=1);

namespace Kwai\JSONAPI;

/**
 * Class Attribute
 *
 * This attribute is used to map a property to a JSON:API value in the
 * attributes object.
 */
#[\Attribute(\Attribute::TARGET_METHOD | \Attribute::TARGET_PROPERTY)]
class Attribute
{
    /**
     * The name can be used to map the property to another name in the JSON:API
     * resource. When applied to a method, the name argument is required.
     *
     * @param string|null $name
     */
    public function __construct(
        private ?string $name = null
    ) {
    }
}
