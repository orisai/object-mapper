# Object Mapper

Raw data mapping to validated objects

Ideal for validation of API POST data, configurations, serialized and any other raw data and automatic mapping
of them to type-safe objects.

## Content

- [Setup](#setup)
- [Quick start](#quick-start)
- [Processing](#processing)
- [Rules](#rules)
	- [Simple types](#simple-types)
		- [bool](#bool-rule)
		- [enum - from array](#enum---from-array-rule)
		- [float](#float-rule)
		- [instanceof](#instanceof-rule)
		- [int](#int-rule)
		- [mixed](#mixed-rule)
		- [null](#null-rule)
		- [object](#object-rule)
		- [scalar](#scalar-rule)
		- [string](#string-rule)
	- [Composed types](#composed-types)
		- [All of rules - &&](#all-of-rules---)
		- [Any of rules - ||](#any-of-rules---)
		- [Array of keys and items](#array-of-keys-and-items-rule)
		- [List of items](#list-of-items-rule)
	- [Value objects](#value-objects)
        - [BackedEnum](#backedenum-rule)
		- [DateTime](#datetime-rule)
		- [MappedObject](#mappedobject-rule)
		- [URL](#url-rule)
- [Optional fields and default values](#optional-fields-and-default-values)
- [Allow unknown fields](#allow-unknown-fields)
- [Mapping field names to properties](#mapping-field-names-to-properties)
- [Processing modes](#processing-modes)
	- [All fields are required](#all-fields-are-required)
	- [No fields are required](#no-fields-are-required)
- [Callbacks](#callbacks)
	- [Mapped object callbacks](#mapped-object-callbacks)
	- [Field callbacks](#field-callbacks)
	- [Returned value](#returned-value)
	- [Dependencies](#dependencies)
	- [Context](#callback-context)
- [Annotations and attributes](#annotations-and-attributes)
- [Object creator](#object-creator)

## Setup

Basic:

```php
use Orisai\ObjectMapper\Attributes\AttributesMetaSource;
use Orisai\ObjectMapper\Meta\ArrayMetaCache;
use Orisai\ObjectMapper\Meta\DefaultMetaResolverFactory;
use Orisai\ObjectMapper\Meta\DefaultMetaSourceManager;
use Orisai\ObjectMapper\Meta\MetaLoader;
use Orisai\ObjectMapper\Processing\DefaultObjectCreator;
use Orisai\ObjectMapper\Processing\DefaultProcessor;
use Orisai\ObjectMapper\Rules\DefaultRuleManager;

$sourceManager = new DefaultMetaSourceManager();
$sourceManager->addSource(new AttributesMetaSource());
$ruleManager = new DefaultRuleManager();
$cache = new ArrayMetaCache();
$resolverFactory = new DefaultMetaResolverFactory($ruleManager);
$metaLoader = new MetaLoader($cache, $sourceManager, $resolverFactory);

$processor = new DefaultProcessor(
	$metaLoader,
	$ruleManager,
	new DefaultObjectCreator(),
);
```

With Nette:

```neon
services:
	orisai.objectMapper.metaSourceManager:
		factory: Orisai\ObjectMapper\Meta\DefaultMetaSourceManager
		setup:
			- addSource(Orisai\ObjectMapper\Attributes\AttributesMetaSource())
	orisai.objectMapper.metaCache: Orisai\ObjectMapper\Bridge\NetteCache\NetteMetaCache(debugMode: %debugMode%)
	orisai.objectMapper.metaResolver.factory: Orisai\ObjectMapper\Meta\DefaultMetaResolverFactory
	orisai.objectMapper.metaLoader: Orisai\ObjectMapper\Meta\MetaLoader
	orisai.objectMapper.ruleManager:
		factory: Orisai\ObjectMapper\Rules\DefaultRuleManager
	orisai.objectMapper.objectCreator: Orisai\ObjectMapper\Bridge\NetteDI\LazyObjectCreator
	orisai.objectMapper.processor: Orisai\ObjectMapper\Processing\DefaultProcessor
```

## Quick start

After you have finished [setup](#setup), define a mapped object:

```php
use Orisai\ObjectMapper\MappedObject;
use Orisai\ObjectMapper\Attributes\Expect\StringValue;

final class UserInput extends MappedObject
{

	/** @StringValue(notEmpty=true) */
	public string $firstName;

	/** @StringValue(notEmpty=true) */
	public string $lastName;

```

Map data to the object:

```php
use Orisai\ObjectMapper\Exception\InvalidData;
use Orisai\ObjectMapper\Printers\ErrorVisualPrinter;
use Orisai\ObjectMapper\Printers\TypeToStringConverter;
use Orisai\ObjectMapper\Processing\DefaultProcessor;

$processor = new DefaultProcessor(...);
$errorPrinter = new ErrorVisualPrinter(new TypeToStringConverter());

$data = [
	'firstName' => 'Tony',
	'lastName' => 'Stark',
];

try {
	$user = $processor->process($data, UserInput::class);
} catch (InvalidData $exception) {
	$error = $errorPrinter->printError($exception);
	throw new Exception("Validation failed due to following error:\n$error");
}

echo "User name is: {$user->firstName} {$user->lastName}";
```

## Processing

Call `$processor->process()` to validate `$data`, instantiate `$objectClass` and map `$data` to `$object`.

In case of an error, handle `InvalidData` exception and print errors with `ErrorPrinter->printError()`.

```php
$data = [/* ... */]; // Data mapped to object
$objectClass = ExampleObject::class; // class-string<MappedObject>

try {
	$object = $processor->process($data, $objectClass); // instance of $objectClass
} catch (InvalidData $exception) {
	$error = $errorPrinter->printError($exception);
	throw new Exception("Validation failed due to following error:\n$error");
}
```

## Rules

### Simple types

### bool rule

Expects bool

```php
use Orisai\ObjectMapper\Attributes\Expect\BoolValue;
use Orisai\ObjectMapper\MappedObject;

final class BoolInput extends MappedObject
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
$input = $processor->process($data, BoolInput::class); // BoolInput
```

Parameters:

- `castBoolLike`
	- accepts also `0` (int|string), `1` (int|string), `'true'` (string, any case), `'false'` (string, any case)
	- value is cast to respective bool value
	- default `false` - bool-like are not cast

### enum - from array rule

> For PHP 8.1+, check [BackedEnum rule](#backedenum-rule)

Expects any of values from given list

```php
use Orisai\ObjectMapper\Attributes\Expect\ArrayEnumValue;
use Orisai\ObjectMapper\MappedObject;

final class ArrayEnumInput extends MappedObject
{

    public const Values = [
        'first' => 1,
        'second' => 2,
        'third' => 3,
    ];

    /**
     * @ArrayEnumValue(Input::Values)
     */
    public int $field;

    /**
     * @ArrayEnumValue(values=Input::Values, useKeys=true)
     */
    public string $anotherField;

}
```

```php
$data = [
	'field' => 1,
	'anotherField' => 'first',
];
$input = $processor->process($data, ArrayEnumInput::class); // ArrayEnumInput
```

Parameters:

- `useKeys`
	- use keys for enumeration instead of values
	- default `false` - values are used for enumeration

### float rule

Expects float or int

- int is cast to float

```php
use Orisai\ObjectMapper\Attributes\Expect\FloatValue;
use Orisai\ObjectMapper\MappedObject;

final class FloatInput extends MappedObject
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
$input = $processor->process($data, FloatInput::class); // FloatInput
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

### instanceof rule

Expects an instance of specified class or interface

- Use [object rule](#object-rule) to accept any object

```php
use Orisai\ObjectMapper\Attributes\Expect\InstanceOfValue;
use Orisai\ObjectMapper\MappedObject;
use stdClass;

final class InstanceofInput extends MappedObject
{

    /** @InstanceOfValue(stdClass::class) */
    public stdClass $field;

}
```

```php
$data = [
	'field' => new stdClass(),
];
$input = $processor->process($data, InstanceofInput::class); // InstanceofInput
```

Parameters:

- `type`
	- type of required instance (class or interface)
	- required
	- e.g. `stdClass::class`

### int rule

Expects int

```php
use Orisai\ObjectMapper\Attributes\Expect\IntValue;
use Orisai\ObjectMapper\MappedObject;

final class IntInput extends MappedObject
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
$input = $processor->process($data, IntInput::class); // IntInput
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

### mixed rule

Expects any value

```php
use Orisai\ObjectMapper\Attributes\Expect\MixedValue;
use Orisai\ObjectMapper\MappedObject;

final class MixedInput extends MappedObject
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
$input = $processor->process($data, MixedInput::class); // MixedInput
```

Parameters:

- no parameters

### null rule

Expects null

```php
use Orisai\ObjectMapper\Attributes\Expect\NullValue;
use Orisai\ObjectMapper\MappedObject;

final class NullInput extends MappedObject
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
$input = $processor->process($data, NullInput::class); // NullInput
```

Parameters:

- `castEmptyString`
	- accepts any string with only empty characters
	- value is cast to null
	- default `false` - empty strings are not cast
	- e.g. `''`, `'   '`, `"\t"` ,`"\t\n\r""`

### object rule

Expects any object

- Use [instanceof rule](#instanceof-rule) to accept instance of specific type

```php
use Orisai\ObjectMapper\Attributes\Expect\ObjectValue;
use Orisai\ObjectMapper\MappedObject;

final class ObjectInput extends MappedObject
{

    /** @ObjectValue() */
    public object $field;

}
```

```php
$data = [
	'field' => $anyObject,
];
$input = $processor->process($data, ObjectInput::class); // ObjectInput
```

Parameters:

- no parameters

### scalar rule

Expects any scalar value - int|float|string|bool

```php
use Orisai\ObjectMapper\Attributes\Expect\ScalarValue;
use Orisai\ObjectMapper\MappedObject;

final class ScalarInput extends MappedObject
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
$input = $processor->process($data, ScalarInput::class); // ScalarInput
```

Parameters:

- no parameters

### string rule

Expects string

```php
use Orisai\ObjectMapper\Attributes\Expect\StringValue;
use Orisai\ObjectMapper\MappedObject;

final class StringInput extends MappedObject
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
$input = $processor->process($data, StringInput::class); // StringInput
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

### Composed types

### All of rules - &&

Expects all rules to match

- After first failure is validation terminated, other rules are skipped
- Rules are executed from first to last
- Output value of each rule is input value of the next rule
- Acts as `&&` operator

```php
use Orisai\ObjectMapper\Attributes\Expect\AllOf;
use Orisai\ObjectMapper\Attributes\Expect\StringValue;
use Orisai\ObjectMapper\Attributes\Expect\Url;
use Orisai\ObjectMapper\MappedObject;

final class AllOfInput extends MappedObject
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
$input = $processor->process(['field' => 'https://example.com'], AllOfInput::class); // AllOfInput
```

Parameters:

- `rules`
	- accepts list of rules by which is the field validated
	- required

### Any of rules - ||

Expects any of rules to match

- Rules are executed from first to last
- Result of first rule which match is used, other rules are skipped
- Acts as `||` operator

```php
use Orisai\ObjectMapper\Attributes\Expect\AnyOf;
use Orisai\ObjectMapper\Attributes\Expect\IntValue;
use Orisai\ObjectMapper\Attributes\Expect\NullValue;
use Orisai\ObjectMapper\Attributes\Expect\StringValue;
use Orisai\ObjectMapper\MappedObject;

final class AnyOfInput extends MappedObject
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
$input = $processor->process(['field' => 'string'], AnyOfInput::class); // AnyOfInput
$input = $processor->process(['field' => 123], AnyOfInput::class); // AnyOfInput
$input = $processor->process(['field' => null], AnyOfInput::class); // AnyOfInput
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

final class ArrayOfInput extends MappedObject
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
$input = $processor->process($data, ArrayOfInput::class); // ArrayOfInput
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

### List of items rule

Expects list

- All keys must be incremental integers

```php
use Orisai\ObjectMapper\Attributes\Expect\ListOf;
use Orisai\ObjectMapper\Attributes\Expect\StringValue;
use Orisai\ObjectMapper\Attributes\Expect\MixedValue;
use Orisai\ObjectMapper\MappedObject;

final class ListOfInput extends MappedObject
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
$input = $processor->process($data, ListOfInput::class); // ListOfInput
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

### Value objects

### BackedEnum rule

Expects value of a `BackedEnum` case

- Returns instance of `BackedEnum`

```php
use Orisai\ObjectMapper\Attributes\Expect\BackedEnumValue;
use Orisai\ObjectMapper\MappedObject;

final class BackedEnumInput extends MappedObject
{

	#[BackedEnumValue(ExampleEnum::class)]
	public ExampleEnum $field;

	#[BackedEnumValue(ExampleEnum::class, allowUnknown: true)]
	public ExampleEnum|null $anotherField;

}

enum ExampleEnum: string
{

	case Foo = 'foo';

}
```

```php
$data = [
	'field' => 'foo',
	'anotherField' => 'unknown value',
];
$input = $processor->process($data, BackedEnumInput::class); // BackedEnumInput
```

Parameters:

- `class`
	- subclass of `BackedEnum` which should be created
	- required
- `allowUnknown`
	- for unknown values rule returns null instead of failing
	- default `false`

### DateTime rule

Expects datetime as a string or int

- Returns instance of `DateTimeInterface`

```php
use DateTime;
use DateTimeImmutable;
use Orisai\ObjectMapper\Attributes\Expect\DateTimeValue;
use Orisai\ObjectMapper\MappedObject;

final class DateTimeInput extends MappedObject
{

    /** @DateTimeValue() */
    public DateTimeImmutable $field;

    /** @DateTimeValue(type=DateTime::class, format="timestamp") */
    public DateTime $anotherField;

}
```

```php
$data = [
	'field' => '2013-04-12T16:40:00-04:00',
	'anotherField' => 1365799200,
];
$input = $processor->process($data, DateTimeInput::class); // DateTimeInput
```

Parameters:

- `type`
	- subclass of `DateTimeInterface` which should be created
	- default `DateTimeImmutable`
- `format`
	- expected date-time format
	- default `DateTimeInterface::ATOM`
		- expects standard ISO 8601 format
		- e.g. `2013-04-12T16:40:00-04:00`
	- accepts any of the formats which are [supported by PHP](https://www.php.net/manual/en/datetime.formats.php)
	- to try auto-parse date-time of unknown format, use format `any`
	- for timestamp use format `timestamp`

### MappedObject rule

Expects array with structure defined by a mapped object

- Returns instance of `MappedObject`
- Mapped object is initialized even when field is not sent at all. It tries to initialize object with an empty
  array (`[]`) to auto-initialize object whose fields are all optional.
	- Object inside a compound rule ([all of](#all-of-rules---), [any of](#any-of-rules---)) is not auto-initialized.
	- Object is also not auto-initialized when [processing mode](#processing-modes) requires none of values or all
	  values to be sent

```php
use Orisai\ObjectMapper\Attributes\Expect\MappedObjectValue;
use Orisai\ObjectMapper\Attributes\Expect\StringValue;
use Orisai\ObjectMapper\MappedObject;

final class MappedObjectInput extends MappedObject
{

    /** @MappedObjectValue(InnerInput::class) */
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
$input = $processor->process($data, MappedObjectInput::class); // MappedObjectInput
```

Parameters:

- `type`
	- subclass of `MappedObject` which should be created
	- required

### URL rule

Expects valid url address

```php
use Orisai\ObjectMapper\Attributes\Expect\Url;
use Orisai\ObjectMapper\MappedObject;

final class UrlInput extends MappedObject
{

    /** @Url() */
    public string $field;

}
```

```php
$data = [
	'field' => 'https://example.com',
];
$input = $processor->process($data, UrlInput::class); // UrlInput
```

Parameters:

- no parameters

## Optional fields and default values

Each field can be made optional by assigning default value to property:

```php
use Orisai\ObjectMapper\Attributes\Expect\StringValue;
use Orisai\ObjectMapper\MappedObject;

final class OptionalInput extends MappedObject
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

final class AnotherOptionalInput extends MappedObject
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

final class NullableVariantsInput extends MappedObject
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
     * @var string|null
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

## Allow unknown fields

Make unknown fields allowed instead of throwing exception.

Unknown fields are removed from data and are not available in mapped object.

```php
use Orisai\ObjectMapper\MappedObject;

final class WithUnknownValuesInput extends MappedObject
{

}
```

```php
use Orisai\ObjectMapper\Processing\Options;

$data = [
	'unknown' => 'any value',
];
$options = new Options();
$options->setAllowUnknownFields();

// No exception is thrown
$input = $processor->process($data, WithUnknownValuesInput::class, $options); // WithUnknownValuesInput
```

## Mapping field names to properties

Keys from input data (fields) are mapped to object properties of the same name, like shown in following example:

```php
use Orisai\ObjectMapper\Attributes\Expect\MixedValue;
use Orisai\ObjectMapper\MappedObject;

final class DefaultMappingInput extends MappedObject
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
$input = $processor->process($data, DefaultMappingInput::class); // DefaultMappingInput
```

We may change that by defining field name for property:

```php
use Orisai\ObjectMapper\Attributes\Expect\MixedValue;
use Orisai\ObjectMapper\Attributes\Modifiers\FieldName;
use Orisai\ObjectMapper\MappedObject;

final class CustomMappingInput extends MappedObject
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
$input = $processor->process($data, CustomMappingInput::class); // CustomMappingInput
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

Send all fields, including these with default values and (with default mode) [auto-initialized](#mappedobject-rule)
mapped objects .

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
under [mapped object rule](#mappedobject-rule). At least empty array (`[]`) should be sent to initialize them.

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

## Callbacks

Define callbacks before and after mapped objects and their fields:

```php
use Orisai\ObjectMapper\MappedObject;
use Orisai\ObjectMapper\Attributes\Callbacks\After;
use Orisai\ObjectMapper\Attributes\Expect\StringValue;
use Orisai\ObjectMapper\Context\FieldContext;

final class WithCallbackInput extends MappedObject
{

	/**
     * @StringValue()
	 * @After("afterField")
	 */
	public string $field;

	public static function afterField(string $value, FieldContext $context): string
	{
		return $value;
	}

}
```

**Never** write to mapped object properties directly in callbacks. Object mapper writes to all properties after all
callbacks are called and overwrites any of set values.

In all callbacks are used [field names](#mapping-field-names-to-properties), not property names.
In [field callbacks](#field-callbacks), current field name can be accessed via [context](#callback-context).

Callbacks can be both static and non-static, object mapper initializes object to call non-static callbacks when needed.

### Mapped object callbacks

Modify and check data before and after processing fields with their rules

Before mapped object

- invoked before processing fields
- accepts raw value, as received from `process()` call
- allowed value types - undefined, `mixed`
- allowed return types - any

After mapped object

- invoked after processing fields and before mapping fields to properties
- accepts value already processed by rules
- allowed value types - `array`
- allowed return types - `array`, `void`, `never`
- [default values](#optional-fields-and-default-values) of fields are available

```php
use Orisai\ObjectMapper\MappedObject;
use Orisai\ObjectMapper\Attributes\Callbacks\Before;
use Orisai\ObjectMapper\Attributes\Callbacks\After;
use Orisai\ObjectMapper\Context\MappedObjectContext;

/**
 * @Before("beforeObject")
 * @After("afterObject")
 */
final class WithMappedObjectCallbacksInput extends MappedObject
{

	/**
     * @param mixed $value
     * @return mixed
     */
	public static function beforeObject($value, MappedObjectContext $context)
	{
		return $value;
	}

	/**
     * @param array<int|string, mixed> $value
     * @return array<int|string, mixed>
     */
	public static function afterObject(array $value, MappedObjectContext $context): array
	{
		return $value;
	}

}
```

### Field callbacks

Modify and check data before and after processing field with its rule

Before field

- invoked before processing field by its rule
- accepts raw value, possibly modified by "before mapped object" callback
- allowed value types - undefined, `mixed`
- allowed return types - any

After field

- invoked after processing field by its rule
- accepts value already processed by field rule
- allowed value types - any - should be compatible with value returned by rule
- allowed return types - any - should be compatible with property type

```php
use Orisai\ObjectMapper\MappedObject;
use Orisai\ObjectMapper\Attributes\Callbacks\Before;
use Orisai\ObjectMapper\Attributes\Callbacks\After;
use Orisai\ObjectMapper\Attributes\Expect\StringValue;
use Orisai\ObjectMapper\Context\FieldContext;

final class WithFieldCallbacksInput extends MappedObject
{

	/**
	 * @Before("beforeField")
     * @StringValue()
	 * @After("afterField")
	 */
	public string $field;

	/**
     * @param mixed $value
     * @return mixed
     */
	public static function beforeField($value, FieldContext $context)
	{
		return $value;
	}

	public static function afterField(string $value, FieldContext $context): string
	{
		return $value;
	}

}
```

Field callbacks are called only when field is sent. Callback is not invoked for default value.

```php
use Orisai\ObjectMapper\MappedObject;
use Orisai\ObjectMapper\Attributes\Callbacks\After;
use Orisai\ObjectMapper\Attributes\Expect\StringValue;
use Orisai\ObjectMapper\Context\FieldContext;

final class WithNotInvokedCallbackInput extends MappedObject
{

	/**
     * @StringValue()
	 * @After("afterField")
	 */
	public string $field = 'default';

	public static function afterField(string $value, FieldContext $context): string
	{
		return $value;
	}

}
```

```php
// Callback IS NOT invoked
$input = $processor->process([], WithNotInvokedCallbackInput::class);
// Callback IS invoked
$input = $processor->process(['field' => 'new value'], WithNotInvokedCallbackInput::class);
```

### Returned value

Callbacks are by default expected to return a value:

```php
use Orisai\ObjectMapper\MappedObject;
use Orisai\ObjectMapper\Attributes\Callbacks\After;
use Orisai\ObjectMapper\Attributes\Expect\MixedValue;

final class WithReturningCallbackInput extends MappedObject
{

	/**
     * @var mixed
     * @MixedValue()
     * @After("afterField")
     */
	public $field;

	/**
     * @param mixed $value
     * @return mixed
     */
	public static function afterField($value)
	{
		return $value;
	}

}
```

We may change that by defining `void` or `never` return type:

```php
use Orisai\ObjectMapper\MappedObject;
use Orisai\ObjectMapper\Attributes\Callbacks\After;
use Orisai\ObjectMapper\Attributes\Expect\StringValue;
use Orisai\ObjectMapper\Exception\ValueDoesNotMatch;
use Orisai\ObjectMapper\Types\Value;

final class WithNotReturningCallbackInput extends MappedObject
{

	/**
     * @StringValue()
     * @After("afterRemoved")
     */
	public string $removed;

	public static function afterRemoved(string $value): void
	{
		throw ValueDoesNotMatch::createFromString('Field is removed', Value::of($value));
	}

}
```

### Dependencies

> To use this feature, check [object creator](#object-creator)

Mapped objects can request dependencies in constructor for extended validation in callbacks:

```php
use Orisai\ObjectMapper\MappedObject;
use Orisai\ObjectMapper\Attributes\Callbacks\After;
use Orisai\ObjectMapper\Attributes\Expect\MixedValue;
use Orisai\ObjectMapper\Exception\ValueDoesNotMatch;
use Orisai\ObjectMapper\Types\Value;

final class WithComplexCallbackInput extends MappedObject
{

	private ExampleService $service;

	public function __construct(ExampleService $service)
	{
		$this->service = $service;
	}

	/**
     * @MixedValue()
     * @After("afterField")
     */
	public $field;

	/**
     * @param mixed $value
     * @return mixed
     */
	public function afterField($value)
	{
		if (!$this->service->valueMatchesCriteria($value)) {
			throw ValueDoesNotMatch::createFromString('Value does not match criteria ABC.', Value::of($value))
		}

		return $value;
	}

}
```

### Callback context

Both [mapped object callbacks](#mapped-object-callbacks) and [field callbacks](#field-callbacks) have additional context
available as a second parameter, for extended processing:

Mapped object and field contexts

```php
$context->getProcessor(); // Processor
$context->getOptions(); // Options
$context->shouldMapDataToObjects(); // bool
$context->getType(); // Type
```

Field context

```php
$context->hasDefaultValue(); // bool
$context->getDefaultValue(); // mixed|exception
$context->getFieldName(); // int|string
$context->getPropertyName(); // string
```

## Annotations and attributes

Since PHP 8.0 annotations can be written as attributes.

```php
use Orisai\ObjectMapper\Attributes\Expect\MixedValue;
use Orisai\ObjectMapper\MappedObject;

final class WithAnnotationsAndAttributesInput extends MappedObject
{

    /** @MixedValue() */
    public mixed $usesAnnotation;

    #[MixedValue()]
    public mixed $usesAttribute;

}
```

```php
$data = [
	'usesAnnotation' => 'value',
	'usesAttribute' => 'value'
];
$input = $processor->process($data, WithAnnotationsAndAttributesInput::class); // WithAnnotationsAndAttributesInput
```

## Object creator

Class responsible for creating objects and injecting [dependencies](#dependencies)
is `Orisai\ObjectMapper\Processing\ObjectCreator`. Default
implementation `Orisai\ObjectMapper\Processing\DefaultObjectCreator` does not have ability to inject dependencies, and
we have to use different one for that use-case:

- `Orisai\ObjectMapper\Bridge\NetteDI\LazyObjectCreator` - injects autowired dependencies
  from [Nette DIC](https://github.com/nette/di)
- Implement `Orisai\ObjectMapper\Processing\ObjectCreator` ourself
