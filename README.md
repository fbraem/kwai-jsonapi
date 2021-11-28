# kwai-jsonapi
A JSON:API serializer for PHP classes using PHP attributes.

> Currently, this library has no support for [links](https://jsonapi.org/format/#document-links).

## Installation

```` 
composer require kwai/jsonapi
````

## Requirements
PHP attributes are used to serialize a PHP class to a JSONAPI resource. So, the 
PHP version must be at least 8.0. There are no other external dependencies.

## Documentation

### #[JSONAPI/Resource]
The "JSONAPI/Resource" attribute is used to set the type of the resource. The type
argument is required. This attribute can only be applied to a class. A class
must have an **id** property.

````php
use Kwai\JSONAPI;

#[JSONAPI/Resource(type: 'people')]
class Person 
{
    public function __construct(
        // We need at least an id property.
        private string $id,
    )
}
````

### #[JSONAPI/Attribute]
The "JSONAPI/Attribute" attribute is used to set an attribute of a resource. This 
attribute can be applied to a property or a method of a class. The name argument 
can be used to give a name to the property. When applied to a method, the name 
argument is required.

````php
use Kwai\JSONAPI;

#[JSONAPI/Resource(type: 'people')]
class Person 
{
    public function __construct(
        private string $id,
        #[JSONAPI/Attribute]
        private string $name,
        private int $age,
    ) {
    }
    
    #[JSONAPI/Attribute(name: 'age')]
    public function getAge(): int
    {
        return $this->age;
    }
}
````
Properties may be private. A method must be public.

With a Person instance like this:

````php
$person = new Person(
    id: '1',
    name: 'Jigoro Kano',
    age: 77,
);
````
The result will be:

````json
{
  "data": {
    "type": "people",
    "id": "1",
    "attributes": {
      "name": "Jigoro Kano",
      "age": 77
    }
  }
}
````

### #[JSONAPI/Relationship]
The "JSONAPI/Relationship" is used to map a relationship. This attribute can be 
applied to a property or a method. The name argument can be used to give a name 
to the relationship. When no name argument is set for a property, the name of 
the property will be used. When applied to a method, the name argument is
required.

````php
#[JSONAPI\Resource(type:'athletes')]
class Athlete
{
    public function __construct(
        private string $id,
        #[JSONAPI\Attribute]
        private string $name,
        #[JSONAPI\Relationship]
        private Country $country,
    ) {
    }
}
````
Properties may be private. A method must be public.

The linked resource must contain a JSONAPI\Resource attribute. If not, a 
JSONAPI\Exception will be thrown. A relationship can also be an array.

With the given PHP code:

````php
$country = new Country(
    id: '1',
    code: 'BEL',
);
$athlete = new Athlete(
    id: '1',
    name: 'Ingrid Berghmans',
    country: $country
)
````

The result of the serializing will be:

````json
{
  "data": {
    "type": "athletes",
    "id": "1",
    "attributes": {
      "name": "Ingrid Berghmans"
    },
    "relationships": {
      "country": {
        "data": {
          "type": "countries",
          "id": "1"
        }
      }
    }
  },
  "included": [
    {
      "type": "countries",
      "id": "1",
      "attributes": {
        "code": "BEL"
      }
    }
  ]
}
````

### Serializing
The JSONAPI\Document class is used to serialize an object or an array to a
JSON:API structure.

````php
use Kwai\JSONAPI;

$person = new Person(
    id: '1',
    name: 'Jigoro Kano',
    age: 77,
);

try {
    $jsonapi = JSONAPI\Document::createFromObject($person)->serialize();
    // Send $jsonapi to the client...
} catch (JSONAPI\Exception $e) {
    // An exception occurred while serializing the PHP object.
}
````

### Meta
Meta information can be set with the setMeta method.

````php
    try {
        $jsonapi =
            JSONAPI\Document::createFromObject($person)
                ->setMeta('count', 1)
                ->serialize();
    } catch (JSONAPI\Exception $e) {
        // Handle exception...
    }
````
