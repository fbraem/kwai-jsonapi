<?php
declare(strict_types=1);

namespace Kwai\JSONAPI;

use ReflectionAttribute;
use ReflectionClass;
use ReflectionException;

/**
 * Class Document
 *
 * A class for serializing PHP classes to a JSON:API representation.
 */
final class Document
{
    /**
     * The array for the meta object.
     */
    private array $meta = [];

    /**
     * The array with all included resources
     */
    private array $included = [];

    /**
     * Constructor.
     *
     * The constructor is private. Use the factory methods createFromArray or
     * createFromObject to create an instance of the Document class.
     *
     * @param object|array $data
     */
    private function __construct(
        private object|array $data
    ) {
    }

    /**
     * Factory method to create a JSON:API resource from an array.
     *
     * @param array $arr
     * @return Document
     */
    public static function createFromArray(array $arr): Document
    {
        return new Document($arr);
    }

    /**
     * Factory method to create a JSON:API resource for an object.
     *
     * @param object $obj
     * @return Document
     */
    public static function createFromObject(object $obj): Document
    {
        return new Document($obj);
    }

    /**
     * Set value(s) in the meta object.
     * When an array is passed, it will be merged with the current content
     * of the meta object.
     *
     * @param string|array $keyOrArray
     * @param mixed        $value
     * @return Document
     */
    public function setMeta(string|array $keyOrArray, mixed $value = null): Document
    {
        if (is_array($keyOrArray)) {
            $this->meta = array_merge($this->meta, $keyOrArray);
        } else {
            $this->meta[$keyOrArray] = $value;
        }
        return $this;
    }

    /**
     * Serializes the resource(s) to a JSONAPI structure.
     *
     * @throws Exception
     */
    public function serialize(): string
    {
        $json = [];
        if (count($this->meta) > 0) {
            $json['meta'] = $this->meta;
        }

        if (is_array($this->data)) {
            $json['data'] = $this->transformArray($this->data);
        } else {
            $json['data'] = $this->transformObject($this->data);
        }

        if (count($this->included) > 0) {
            $json['included'] = $this->included;
        }
        return json_encode($json);
    }

    /**
     * Transforms an array
     *
     * @throws Exception
     */
    private function transformArray(array $arr): array
    {
        $result = [];
        foreach ($arr as $resource) {
            $result[] = $this->transformObject($resource);
        }
        return $result;
    }

    /**
     * Transforms an object
     *
     * @throws Exception
     */
    private function transformObject(object $obj): array
    {
        $result = [];
        try {
            $ref = new ReflectionClass($obj);
        } catch (ReflectionException $e) {
            throw new Exception("Can't reflect resource with class " . get_class($obj));
        }

        // Get the type
        $attributes = $ref->getAttributes(Resource::class);
        if (count($attributes) > 0) {
            $result['type'] = $attributes[0]->getArguments()['type']
                ?? throw new Exception('Type not set for resource');
        } else {
            throw new Exception('Resource misses a Resource attribute');
        }

        // Get the id
        $result['id'] = $this->getId($ref, $attributes[0], $obj);

        // Get the attributes
        $result['attributes'] = array_merge(
            $this->getAttributesFromProperties($ref, $obj),
            $this->getAttributesFromMethods($ref, $obj)
        );

        // Get the relationships
        $relationships = array_merge(
            $this->getRelationshipsFromProperties($ref, $obj),
            $this->getRelationshipsFromMethods($ref, $obj)
        );
        if (count($relationships) > 0) {
            $result['relationships'] = $relationships;
        }

        return $result;
    }

    /**
     * Checks all properties for the JSONAPI\Attribute and puts them in the
     * attributes object.
     *
     * @param ReflectionClass $ref
     * @param object           $obj
     * @return array
     */
    private function getAttributesFromProperties(ReflectionClass $ref, object $obj): array
    {
        $attributes = [];
        $properties = $ref->getProperties();
        foreach ($properties as $property) {
            $propertyAttributes = $property->getAttributes(Attribute::class);
            if (count($propertyAttributes) == 0) {
                continue;
            }
            $attributeName = $propertyAttributes[0]->getArguments()['name'] ?? $property->getName();
            $property->setAccessible(true);
            $attributes[$attributeName] = $property->getValue($obj);
        }
        return $attributes;
    }

    /**
     * Check all methods for the JSONAPI\Attribute attribute. The result
     * of the method call will be used to store it in the attributes object.
     *
     * @param ReflectionClass $ref
     * @param object          $obj
     * @return array
     * @throws Exception
     */
    private function getAttributesFromMethods(ReflectionClass $ref, object $obj): array
    {
        $attributes = [];
        $methods = $ref->getMethods();
        foreach ($methods as $method) {
            $methodAttributes = $method->getAttributes(Attribute::class);
            if (count($methodAttributes) == 0) {
                continue;
            }

            $attributeName = $methodAttributes[0]->getArguments()['name']
                ?? throw new Exception('Missing attribute name on method ' . $method->getName());
            if (!$method->hasReturnType()) {
                throw new Exception('Missing return type on method ' . $method->getName());
            }

            try {
                $attributes[$attributeName] = $method->invoke($obj);
            } catch (ReflectionException $e) {
                throw new Exception(
                    'A reflection exception occurred while executing method ' .
                    $method->getName() .
                    ' - ' .
                    $e->getMessage()
                );
            }
        }
        return $attributes;
    }

    /**
     * Check all properties for the JSONAPI\Relationship attribute. The value
     * of the property will be used as linked resource.
     *
     * @throws Exception
     */
    private function getRelationshipsFromProperties(ReflectionClass $ref, $obj): array
    {
        $relationships = [];

        $properties = $ref->getProperties();
        foreach ($properties as $property) {
            $propertyAttributes = $property->getAttributes(Relationship::class);
            if (count($propertyAttributes) == 0) {
                continue;
            }

            $property->setAccessible(true);
            $relationshipName = $propertyAttributes[0]->getArguments()['name'] ?? $property->getName();
            $value = $property->getValue($obj);
            if ($value || is_array($value)) {
                $relationships[$relationshipName] = $this->processLinkedResource($value);
            }
        }
        return $relationships;
    }

    /**
     * Check all methods for the JSONAPI\Relationship attribute. The return
     * value of the method will be used as linked resource.
     *
     * @throws Exception
     */
    private function getRelationshipsFromMethods(ReflectionClass $ref, $obj): array
    {
        $relationships = [];

        $methods = $ref->getMethods();
        foreach ($methods as $method) {
            $methodAttributes = $method->getAttributes(Relationship::class);
            if (count($methodAttributes) == 0) {
                continue;
            }

            $relationshipName = $methodAttributes[0]->getArguments()['name']
                ?? throw new Exception('Missing attribute name on method ' . $method->getName());
            if (!$method->hasReturnType()) {
                throw new Exception('Missing return type on method ' . $method->getName());
            }

            try {
                $value = $method->invoke($obj);
            } catch (ReflectionException $e) {
                throw new Exception(
                    'A reflection exception occurred while executing method ' .
                    $method->getName() .
                    ' - ' .
                    $e->getMessage()
                );
            }

            if ($value || is_array($value)) {
                $relationships[$relationshipName] = $this->processLinkedResource($value);
            }
        }
        return $relationships;
    }

    /**
     * Process linked resources to put them in the relationship object
     * and add them to the included array.
     *
     * @param mixed $value
     * @return array
     * @throws Exception
     */
    private function processLinkedResource(mixed $value): array
    {
        $result = [];
        if (is_array($value)) {
            $result['data'] = [];
            foreach ($value as $linkedResource) {
                if ($linkedResource) {
                    $relationshipData = $this->transformObject($linkedResource);
                    $this->include($relationshipData);
                    // Attributes/relationships are not stored in the data, so unset it here.
                    unset($relationshipData['attributes']);
                    unset($relationshipData['relationships']);
                    $result['data'][] = $relationshipData;
                }
            }
        } else {
            $relationshipData = $this->transformObject($value);
            $result = [
                'data' => [
                    'type' => $relationshipData['type'],
                    'id' => $relationshipData['id']
                ]
            ];
            $this->include($relationshipData);
        }
        return $result;
    }

    /**
     * Add the resource to the included array when it is not yet stored.
     *
     * @param array $data
     */
    private function include(array $data)
    {
        // Just add it once.
        if (count(array_filter(
            $this->included,
            fn ($item) => $item['type'] == $data['type'] && $item['id'] == $data['id']
        )) == 0) {
            $this->included[] = $data;
        }
    }

    /**
     * Try to get the value for the id of the resource.
     *
     * @param ReflectionClass      $ref
     * @param ReflectionAttribute $resourceAttribute
     * @param object               $obj
     * @return string
     * @throws Exception
     */
    private function getId(ReflectionClass $ref, ReflectionAttribute $resourceAttribute, object $obj): string
    {
        $arguments = $resourceAttribute->getArguments();
        // First check if there is an id argument passed in ResourceAttribute
        if (isset($arguments['id'])) {
            // When a method exists with this name, call it to get the id.
            if ($ref->hasMethod($arguments['id'])) {
                try {
                    $id = $ref->getMethod($arguments['id'])->invoke($obj);
                } catch (ReflectionException $e) {
                    throw new Exception(
                        "Can't get the id property from method " .
                        $arguments['id'] . " of class " . $ref->getName()
                    );
                }
                return $id;
            }
            $propertyName = $arguments['id'];
        } else {
            $propertyName = 'id';
        }

        // Try to find an id property
        try {
            $idProperty = $ref->getProperty($propertyName);
        } catch (\ReflectionException $e) {
            throw new Exception("Can't get an id property from class " . get_class($obj));
        }
        $idProperty->setAccessible(true);
        return (string) $idProperty->getValue($obj);
    }
}
