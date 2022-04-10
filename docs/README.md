# Object Mapper

Raw data mapping to validated objects

## Content

- [Rules](#rules)
	- [All of rules](#all-of-rules)
	- [Any of rules](#any-of-rules)
	- [Array of keys and items](#array-of-keys-and-items-rule)
	- [Bool](#bool-rule)
	- [DateTime](#datetime-rule)
	- [Float](#float-rule)
	- [Instance of type](#instance-of-type-rule)
	- [Int](#int-rule)
	- [List of items](#list-of-items-rule)
	- [Mixed](#mixed-rule)
	- [Null](#null-rule)
	- [Object](#object-rule)
	- [Scalar](#scalar-rule)
	- [String](#string-rule)
	- [Structure / Mapped object](#structure--mapped-object-rule)
	- [Url](#url-rule)
	- [Enum from values](#enum-from-values-rule)
- [Optional fields and default values](#optional-fields-and-default-values)
- [Mapping field names to properties](#mapping-field-names-to-properties)
- [Processing modes](#processing-modes)
	- [All fields are required](#all-fields-are-required)
	- [No fields are required](#no-fields-are-required)

## Rules

### All of rules

Expects all rules to match

- After first failure is validation terminated, other rules are skipped
- Rules are executed from first to last
- Output value of each rule is input value of the next rule

```php
use Orisai\ObjectMapper\Attributes\Expect\AllOf;
use Orisai\ObjectMapper\Attributes\Expect\StringValue;
use Orisai\ObjectMapper\Attributes\Expect\Url;
use Orisai\ObjectMapper\MappedObject;

final class Input extends MappedObject
{

    /**
     * @AllOf({
     *      @Url(),
     *      @StringValue(maxLength=20),
     * })
     */
    public string $field;

}
```

```php
$processor->process(['field' => 'https://example.com'], Input::class); // Input
```

Parameters:

- `rules`
	- accepts list of rules by which is the field validated
	- required

### Any of rules

Expects any of rules to match

- Rules are executed from first to last
- Result of first rule which match is used, other rules are skipped

```php
use Orisai\ObjectMapper\Attributes\Expect\AnyOf;
use Orisai\ObjectMapper\Attributes\Expect\IntValue;
use Orisai\ObjectMapper\Attributes\Expect\NullValue;
use Orisai\ObjectMapper\Attributes\Expect\StringValue;
use Orisai\ObjectMapper\MappedObject;

final class Input extends MappedObject
{

    /**
     * @var string|int|null
     * @AnyOf({
     *      @StringValue(),
     *      @IntValue(),
     *      @NullValue(),
     * })
     */
    public $field;

}
```

```php
$processor->process(['field' => 'string'], Input::class); // Input
$processor->process(['field' => 123], Input::class); // Input
$processor->process(['field' => null], Input::class); // Input
```

Parameters:

- `rules`
	- accepts list of rules by which is the field validated
	- required

### Array of keys and items rule

Expects array

```php
use Orisai\ObjectMapper\Attributes\Expect\ArrayOf;
use Orisai\ObjectMapper\Attributes\Expect\IntValue;
use Orisai\ObjectMapper\Attributes\Expect\MixedValue;
use Orisai\ObjectMapper\Attributes\Expect\StringValue;
use Orisai\ObjectMapper\MappedObject;

final class Input extends MappedObject
{

    /**
     * @var array<mixed>
     * @ArrayOf(
     *      @MixedValue()
     * )
     */
    public array $field;

    /**
     * @var non-empty-array<string, int>
     * @ArrayOf(
     *      item=@IntValue(),
     *      key=@StringValue(),
     *      minItems=1,
     *      maxItems=100,
     *      mergeDefaults=true,
     * )
     */
    public array $anotherField = ['key' => 1];

}
```

```php
$data = [
	'field' => ['anything', 1234, true, null],
	'anotherField' => ['key1' => 1, 'key2' => 2],
];
$processor->process($data, Input::class); // Input
```

Parameters:

- `item`
	- accepts rule which is used to validate items
	- required
- `key`
	- accepts rule which is used to validate items
	- default `null` - keys are not validated
- `minItems`
	- minimal count of items
	- default `null` - no limit
	- e.g. `10`
- `maxItems`
	- maximal count of items
	- if limit is exceeded then no other validations of array are performed
	- default `null` - no limit
	- e.g. `100`
	- validation fails immediately when limit is exceeded
- `mergeDefaults`
	- merge default value into array after it is validated
	- default `false` - default is not merged

### Bool rule

Expects bool

```php
use Orisai\ObjectMapper\Attributes\Expect\BoolValue;
use Orisai\ObjectMapper\MappedObject;

final class Input extends MappedObject
{

    /** @BoolValue() */
    public bool $field;

    /** @BoolValue(castBoolLike=true) */
    public bool $anotherField;

}
```

```php
$data = [
	'field' => true,
	'anotherField' => 1,
];
$processor->process($data, Input::class); // Input
```

Parameters:

- `castBoolLike`
	- accepts also `0` (int), `1` (int), `'true'` (string, any case), `'false'` (string, any case)
	- value is cast to respective bool value
	- default `false` - bool-like are not cast

### DateTime rule

Expects datetime as a string or int

- Returns instance of `DateTimeInterface`

```php
use DateTimeImmutable;
use Orisai\ObjectMapper\Attributes\Expect\DateTime;
use Orisai\ObjectMapper\MappedObject;

final class Input extends MappedObject
{

    /** @DateTime() */
    public DateTimeImmutable $field;

    /** @DateTime(type=\DateTime::class, format="timestamp") */
    public \DateTime $anotherField;

}
```

```php
$data = [
	'field' => '2013-04-12T16:40:00-04:00',
	'anotherField' => 1365799200,
];
$processor->process($data, Input::class); // Input
```

Parameters:

- `format`
	- expected date-time format
	- default `DateTimeInterface::ATOM`
		- expects standard ISO 8601 format
		- e.g. `2013-04-12T16:40:00-04:00`
	- accepts any of the formats which are [supported by PHP](https://www.php.net/manual/en/datetime.formats.php)
	- to try auto-parse date-time of unknown format, use format `any`
	- for timestamp use format `timestamp`

### Float rule

Expects float or int

- Int is cast to float

```php
use Orisai\ObjectMapper\Attributes\Expect\FloatValue;
use Orisai\ObjectMapper\MappedObject;

final class Input extends MappedObject
{

    /** @FloatValue() */
    public float $field;

    /**
     * @var float<1.1, 100.1>
     * @FloatValue(min=1.1, max=100.1, unsigned=false, castNumericString=true)
     */
    public float $anotherField;

}
```

```php
$data = [
	'field' => 666.666,
	'anotherField' => '6.66',
];
$processor->process($data, Input::class); // Input
```

Parameters:

- `min`
	- minimal accepted value
	- default `null` - no limit
	- e.g. `10.0`
- `max`
	- maximal accepted value
	- default `null` - no limit
	- e.g. `100.0`
- `unsigned`
	- accepts only numbers without minus sign
	- default `false` - both positive and negative numbers are accepted
	- act same way as `min: 0`
- `castNumericString`
	- accepts also numeric strings (float and int)
	- value is cast to respective float value
	- default `false` - numeric strings are not cast

### Instance of type rule

Expects an instance of specified class or interface

- Use [Object rule](#object-rule) to accept any object

```php
use Orisai\ObjectMapper\Attributes\Expect\InstanceValue;
use Orisai\ObjectMapper\MappedObject;
use stdClass;

final class Input extends MappedObject
{

    /** @InstanceValue(stdClass::class) */
    public stdClass $field;

}
```

```php
$data = [
	'field' => new stdClass(),
];
$processor->process($data, Input::class); // Input
```

Parameters:

- `type`
	- type of required instance (class or interface)
	- required
	- e.g. `stdClass::class`

### Int rule

Expects int

```php
use Orisai\ObjectMapper\Attributes\Expect\IntValue;
use Orisai\ObjectMapper\MappedObject;

final class Input extends MappedObject
{

    /** @IntValue() */
    public int $field;

    /**
     * @var int<1, 100>
     * @IntValue(min=1, max=100, unsigned=false, castNumericString=true)
     */
    public int $anotherField;

}
```

```php
$data = [
	'field' => 666,
	'anotherField' => '42',
];
$processor->process($data, Input::class); // Input
```

Parameters:

- `min`
	- minimal accepted value
	- default `null` - no limit
	- e.g. `10`
- `max`
	- maximal accepted value
	- default `null` - no limit
	- e.g. `100`
- `unsigned`
	- accepts only numbers without minus sign
	- default `false` - both positive and negative numbers are accepted
	- act same way as `min: 0`
- `castNumericString`
	- accepts also numeric strings (int)
	- value is cast to respective int value
	- default `false` - numeric strings are not cast

### List of items rule

Expects list

- All keys must be incremental integers

```php
use Orisai\ObjectMapper\Attributes\Expect\ListOf;
use Orisai\ObjectMapper\Attributes\Expect\StringValue;
use Orisai\ObjectMapper\Attributes\Expect\MixedValue;
use Orisai\ObjectMapper\MappedObject;

final class Input extends MappedObject
{

    /**
     * @var list<int, mixed>
     * @ListOf(
     *      @MixedValue(),
     * )
     */
    public array $field;

    /**
     * @var non-empty-list<string>
     * @ListOf(
     *      item=@StringValue(),
     *      minItems=1,
     *      maxItems=100,
     *      mergeDefaults=true,
     * )
     */
    public array $anotherField = ['value'];

}
```

```php
$data = [
	'field' => ['anything', 1234, true, null],
	'anotherField' => ['one', 'two'],
];
$processor->process($data, Input::class); // Input
```

Parameters:

- `item`
	- accepts rule which is used to validate items
	- required
- `minItems`
	- minimal count of items
	- default `null` - no limit
	- e.g. `10`
- `maxItems`
	- maximal count of items
	- if limit is exceeded then no other validations of array are performed
	- default `null` - no limit
	- e.g. `100`
	- validation fails immediately when limit is exceeded
- `mergeDefaults`
	- merge default value into array after it is validated
	- default `false` - default is not merged

### Mixed rule

Expects any value

```php
use Orisai\ObjectMapper\Attributes\Expect\MixedValue;
use Orisai\ObjectMapper\MappedObject;

final class Input extends MappedObject
{

    /**
     * @var mixed
     * @MixedValue()
     */
    public $field;

}
```

```php
$data = [
	'field' => 'anything',
];
$processor->process($data, Input::class); // Input
```

Parameters:

- no parameters

### Null rule

Expects null

```php
use Orisai\ObjectMapper\Attributes\Expect\NullValue;
use Orisai\ObjectMapper\MappedObject;

final class Input extends MappedObject
{

    /**
     * @var null
     * @NullValue()
     */
    public $field;

    /**
     * @var null
     * @NullValue(castEmptyString=true)
     */
    public $anotherField;

}
```

```php
$data = [
	'field' => null,
	'anotherField' => '',
];
$processor->process($data, Input::class); // Input
```

Parameters:

- `castEmptyString`
	- accepts any string with only empty characters
	- value is cast to null
	- default `false` - empty strings are not cast
	- e.g. `''`, `'   '`, `"\t"` ,`"\t\n\r""`

### Object rule

Expects any object

- Use [Instance of type rule](#instance-of-type-rule) to accept instance of specific type

```php
use Orisai\ObjectMapper\Attributes\Expect\ObjectValue;
use Orisai\ObjectMapper\MappedObject;

final class Input extends MappedObject
{

    /** @ObjectValue() */
    public object $field;

}
```

```php
$data = [
	'field' => $anyObject,
];
$processor->process($data, Input::class); // Input
```

Parameters:

- no parameters

### Scalar rule

Expects any scalar value - int|float|string|bool

```php
use Orisai\ObjectMapper\Attributes\Expect\ScalarValue;
use Orisai\ObjectMapper\MappedObject;

final class Input extends MappedObject
{

    /**
     * @var int|float|string|bool
     * @ScalarValue()
     */
    public $field;

}
```

```php
$data = [
	'field' => 'any scalar value',
];
$processor->process($data, Input::class); // Input
```

Parameters:

- no parameters

### String rule

Expects string

```php
use Orisai\ObjectMapper\Attributes\Expect\StringValue;
use Orisai\ObjectMapper\MappedObject;

final class Input extends MappedObject
{

    /** @StringValue() */
    public string $field;

    /**
     * @var non-empty-string
     * @StringValue(minLength=1, maxLength=100, notEmpty=true, pattern="/^abc/")
     */
    public string $anotherField;

}
```

```php
$data = [
	'field' => 'string',
	'anotherField' => 'abcdef',
];
$processor->process($data, Input::class); // Input
```

Parameters:

- `minLength`
	- minimal string length
	- default `null` - no limit
	- e.g. `10`
- `maxLength`
	- maximal string length
	- default `null` - no limit
	- e.g. `100`
- `notEmpty`
	- string **must not** contain **only** empty characters
	- default `false` - empty strings are allowed
	- e.g. `''`, `'   '`, `"\t"` ,`"\t\n\r""`
- `pattern`
	- regex pattern which must match
	- default `null` - no validation by pattern
	- e.g. `/[\s\S]/`

### Structure / Mapped object rule

Expects array with structure defined by a mapped object

- Returns instance of `MappedObject`
- Structure is initialized even when field is not sent at all. It tries to initialize structure with an empty
  array (`[]`) to auto-initialize structure whose fields are all optional.
	- Structures inside a compound rule ([all of](#all-of-rules), [any of](#any-of-rules)) are not auto-initialized.
	- Structures are also not auto-initialized when validation mode requires none of values or all values to be sent
	  (including defaults)

```php
use Orisai\ObjectMapper\Attributes\Expect\StringValue;
use Orisai\ObjectMapper\Attributes\Expect\Structure;
use Orisai\ObjectMapper\MappedObject;

final class Input extends MappedObject
{

    /** @Structure(InnerInput::class) */
    public InnerInput $field;

}

class InnerInput extends MappedObject
{

    /**
     * @StringValue()
     */
    public string $field;

}
```

```php
$data = [
	'field' => [
		'field' => 'string',
	],
];
$processor->process($data, Input::class); // Input
```

Parameters:

- `type`
	- subclass of `MappedObject` which should be created
	- required

### Url rule

Expects valid url address

```php
use Orisai\ObjectMapper\Attributes\Expect\Url;
use Orisai\ObjectMapper\MappedObject;

final class Input extends MappedObject
{

    /** @Url() */
    public string $field;

}
```

```php
$data = [
	'field' => 'https://example.com',
];
$processor->process($data, Input::class); // Input
```

Parameters:

- no parameters

### Enum from values rule

Expects any of values from given list

```php
use Orisai\ObjectMapper\Attributes\Expect\ValueEnum;
use Orisai\ObjectMapper\MappedObject;

final class Input extends MappedObject
{

    public const VALUES = [
        'first' => 1,
        'second' => 2,
        'third' => 3,
    ];

    /**
     * @ValueEnum(Input::VALUES)
     */
    public int $field;

    /**
     * @ValueEnum(values=Input::VALUES, useKeys=true)
     */
    public string $anotherField;

}
```

```php
$data = [
	'field' => 1,
	'anotherField' => 'first',
];
$processor->process($data, Input::class); // Input
```

Parameters:

- `useKeys`
	- use keys for enumeration instead of values
	- default `false` - values are used for enumeration

## Optional fields and default values

Each field can be made optional by assigning default value to property:

```php
use Orisai\ObjectMapper\Attributes\Expect\StringValue;
use Orisai\ObjectMapper\MappedObject;

final class Input extends MappedObject
{

    /** @StringValue() */
    public string $field = 'default value';

}
```

Default values are *never validated by rules* and will not appear in validation errors. We may then assign defaults
which are impossible to send:

```php
use Orisai\ObjectMapper\Attributes\Expect\StringValue;
use Orisai\ObjectMapper\MappedObject;

final class Input extends MappedObject
{

    /** @StringValue() */
    public ?string $field = null;

}
```

Properties without type and with no explicit default value always have `null` value by default in PHP. Due to this
object mapper cannot distinguish whether untyped property was meant to have default value of `null` or not just from the
property. Whether field should be required or not is therefore detected from used rules:

```php
use Orisai\ObjectMapper\Attributes\Expect\AllOf;
use Orisai\ObjectMapper\Attributes\Expect\AnyOf;
use Orisai\ObjectMapper\Attributes\Expect\MixedValue;
use Orisai\ObjectMapper\Attributes\Expect\NullValue;
use Orisai\ObjectMapper\Attributes\Expect\StringValue;
use Orisai\ObjectMapper\MappedObject;

final class Input extends MappedObject
{

    /**
     * OPTIONAL - Field allows null
     *
     * @var null
     * @NullValue()
     */
    public $implicitNull;

    /**
     * OPTIONAL - Field allows null
     *          - Identical with $implicitNull - untyped properties are null by default
     *
     * @var null
     * @NullValue()
     */
    public $explicitNull = null;

    /**
     * OPTIONAL - Field allows mixed which includes null
     *
     * @var mixed
     * @MixedValue()
     */
    public $mixed;

    /**
     * OPTIONAL - Field allows null
     *          - MixedValue and multiple levels of AnyOf and AllOf work as well
     *
     * @var string|null
     * @AnyOf({
     *     @StringValue(),
     *     @NullValue(),
     * })
     */
    public $anyOf;

    /**
     * OPTIONAL - Field allows null
     *          - MixedValue and multiple levels of AnyOf and AllOf work as well
     *
     * @var null
     * @AllOf({
     *     @StringValue(),
     *     @NullValue(castEmptyString=true),
     * })
     */
    public $allOf;

    /**
     * REQUIRED - Field does not allow null
     *
     * @var string
     * @StringValue()
     */
    public $string;

}
```

## Mapping field names to properties

Keys from input data (fields) are mapped to object properties of the same name, like shown in following example:

```php
use Orisai\ObjectMapper\Attributes\Expect\MixedValue;
use Orisai\ObjectMapper\MappedObject;

final class Input extends MappedObject
{

    /**
     * @var mixed
     * @MixedValue()
     */
    public $field;

}
```

```php
$data = [
	'field' => 'anything',
];
$processor->process($data, Input::class); // Input
```

We may change that by defining field name for property:

```php
use Orisai\ObjectMapper\Attributes\Expect\MixedValue;
use Orisai\ObjectMapper\Attributes\Modifiers\FieldName;
use Orisai\ObjectMapper\MappedObject;

final class Input extends MappedObject
{

    /**
     * @var mixed
     * @MixedValue()
     * @FieldName("customFieldName")
     */
    public $field;

}
```

We then have to send key from `@FieldName` instead of property name:

```php
$data = [
	'customFieldName' => 'anything',
];
$processor->process($data, Input::class); // Input
```

## Processing modes

Processor requires all fields with no default value to be sent. We may change that and require all fields to be sent or
require no fields at all.

Following mapped object has one required and one optional field (
see [default values](#optional-fields-and-default-values)). By default, you have to send only required field:

```php
use Orisai\ObjectMapper\Attributes\Expect\BoolValue;
use Orisai\ObjectMapper\MappedObject;

final class ModesExampleInput extends MappedObject
{

    /** @BoolValue() */
    public bool $required;

    /** @BoolValue() */
    public bool $optional = true;

}
```

```php
$data = [
	'required' => true,
];

$input = $processor->process($data, ModesExampleInput::class); // ModesExampleInput

$input->required; // true
$input->optional; // true, default
```

### All fields are required

Send all fields, including these with default values and (with default
mode) [auto-initialized](#structure--mapped-object-rule) mapped objects .

```php
use Orisai\ObjectMapper\Processing\Options;
use Orisai\ObjectMapper\Processing\RequiredFields;

$data = [
	'required' => true,
	'optional' => true,
];
$options = new Options();
$options->setRequiredFields(RequiredFields::all());

$input = $processor->process($data, ModesExampleInput::class, $options); // ModesExampleInput

$input->required; // true
$input->optional; // true
```

### No fields are required

We can make all fields optional. This is useful for partial updates, like PATCH requests in REST APIs. Only changed
fields are sent, and we have to check which ones are available with `$mappedObject->isInitialized('property');`.

Unlike with default mode, mapped object are not auto-initialized as described
under [mapped object rule](#structure--mapped-object-rule). At least empty array (`[]`) should be sent to initialize
them.

Even default values, when not sent, are not initialized - they are unset before assigning sent data.

```php
use Orisai\ObjectMapper\Processing\Options;
use Orisai\ObjectMapper\Processing\RequiredFields;

$data = [];
$options = new Options();
$options->setRequiredFields(RequiredFields::none());

$input = $processor->process($data, ModesExampleInput::class, $options); // ModesExampleInput

$input->isInitialized('required'); // false
$input->required; // Error, property is not set
$input->isInitialized('optional'); // false
$input->optional; // Error, property is not set
```
