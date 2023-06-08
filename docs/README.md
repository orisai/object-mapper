# Object Mapper

Raw data mapping to validated objects

Ideal for validation of API POST data, configurations, serialized and any other raw data and automatic mapping
of them to type-safe objects.

## Content

- [Setup](#setup)
- [Quick start](#quick-start)
- [Processing](#processing)
- [Annotations and attributes](#annotations-and-attributes)
- [Rules](#rules)
	- [Simple rules](#simple-rules)
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
	- [Composed rules](#composed-rules)
		- [All of rules - &&](#all-of-rules---)
		- [Any of rules - ||](#any-of-rules---)
		- [Array of keys and items](#array-of-keys-and-items-rule)
		- [List of items](#list-of-items-rule)
	- [Value objects](#value-objects)
		- [BackedEnum](#backedenum-rule)
		- [DateTime](#datetime-rule)
		- [MappedObject](#mappedobject-rule)
	- [Registering custom rules](#registering-custom-rules)
- [Optional fields and default values](#optional-fields-and-default-values)
- [Allow unknown fields](#allow-unknown-fields)
	- [Ambiguous definitions with unknown fields allowed](#ambiguous-definitions-with-unknown-fields-allowed)
- [Mapped properties](#mapped-properties)
	- [Mapping field names to properties](#mapping-field-names-to-properties)
- [Processing modes](#processing-modes)
	- [All fields are required](#all-fields-are-required)
	- [No fields are required](#no-fields-are-required)
- [Callbacks](#callbacks)
	- [Mapped object callbacks](#mapped-object-callbacks)
	- [Field callbacks](#field-callbacks)
	- [Returned value](#returned-value)
	- [Context](#callback-context)
- [Dependencies](#dependencies)
- [Printers](#printers)
	- [Printing errors](#printing-errors)
	- [Printing types](#printing-types)
- [Metadata validation and preloading](#metadata-validation-and-preloading)
- [Tracking input values](#tracking-input-values)
- [Create mapped object without using object mapper](#create-mapped-object-without-using-object-mapper)
- [Types](#types)
	- [ArrayShapeType](#arrayshapetype)
	- [CompoundType](#compoundtype)
	- [EnumType](#enumtype)
	- [GenericArrayType](#genericarraytype)
	- [MappedObjectType](#mappedobjecttype)
	- [MessageType](#messagetype)
	- [SimpleValueType](#simplevaluetype)
	- [ParametrizedType](#parametrizedtype)

## Setup

Install with [Composer](https://getcomposer.org)

```sh
composer require orisai/object-mapper
```

Configure processor:

```php
use Orisai\ObjectMapper\Meta\Cache\ArrayMetaCache;
use Orisai\ObjectMapper\Meta\MetaResolverFactory;
use Orisai\ObjectMapper\Meta\MetaLoader;
use Orisai\ObjectMapper\Meta\Source\AnnotationsMetaSource;
use Orisai\ObjectMapper\Meta\Source\AttributesMetaSource;
use Orisai\ObjectMapper\Meta\Source\DefaultMetaSourceManager;
use Orisai\ObjectMapper\Processing\DefaultDependencyInjectorManager;
use Orisai\ObjectMapper\Processing\DefaultProcessor;
use Orisai\ObjectMapper\Processing\ObjectCreator;
use Orisai\ObjectMapper\Rules\DefaultRuleManager;

$sourceManager = new DefaultMetaSourceManager();
$sourceManager->addSource(new AnnotationsMetaSource()); // For doctrine/annotations
$sourceManager->addSource(new AttributesMetaSource()); // For PHP 8 attributes
$injectorManager = new DefaultDependencyInjectorManager();
$objectCreator = new ObjectCreator($injectorManager);
$ruleManager = new DefaultRuleManager();
$resolverFactory = new MetaResolverFactory($ruleManager, $objectCreator);
$cache = new ArrayMetaCache();
$metaLoader = new MetaLoader($cache, $sourceManager, $resolverFactory);

$processor = new DefaultProcessor(
	$metaLoader,
	$ruleManager,
	$objectCreator,
);
```

Or, if you use Nette, check [orisai/nette-object-mapper](https://github.com/orisai/nette-object-mapper) for
installation.

## Quick start

After you have finished [setup](#setup), define a mapped object:

```php
use Orisai\ObjectMapper\MappedObject;
use Orisai\ObjectMapper\Rules\StringValue;

final class UserInput implements MappedObject
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

$processor = new DefaultProcessor(/* ... */);
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

## Annotations and attributes

All [rule definition](#rules) can be written either as a [doctrine/annotations](https://github.com/doctrine/annotations)
annotation or PHP 8.0+ [attribute](https://www.php.net/manual/en/language.attributes.overview.php)

```php
use Orisai\ObjectMapper\MappedObject;
use Orisai\ObjectMapper\Rules\MixedValue;

final class WithAnnotationsAndAttributesInput implements MappedObject
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
$input = $processor->process($data, WithAnnotationsAndAttributesInput::class); // WithAnnotationsAndAttributesInput(usesAnnotation: 'value', usesAttribute: 'value')
```

## Rules

### Simple rules

### bool rule

Expects bool

```php
use Orisai\ObjectMapper\MappedObject;
use Orisai\ObjectMapper\Rules\BoolValue;

final class BoolInput implements MappedObject
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
	'anotherField' => 0,
];
$input = $processor->process($data, BoolInput::class);
// $input == BoolInput(field: true, anotherField: false)
```

Parameters:

- `castBoolLike`
	- accepts also `0` (int|string), `1` (int|string), `'true'` (string, any case), `'false'` (string, any case)
	- value is cast to respective bool value
	- default `false` - bool-like are not cast

### enum - from array rule

> For PHP 8.1+, check [BackedEnum rule](#backedenum-rule)

Expects any of cases from given list

```php
use Orisai\ObjectMapper\MappedObject;
use Orisai\ObjectMapper\Rules\ArrayEnumValue;

final class ArrayEnumInput implements MappedObject
{

    public const Cases = [
        'first' => 1,
        'second' => 2,
        'third' => 3,
    ];

    /**
     * @ArrayEnumValue(ArrayEnumInput::Cases)
     */
    public int $field;

    /**
     * @ArrayEnumValue(cases=ArrayEnumInput::Cases, useKeys=true)
     */
    public string $usesKeysField;

    /**
     * @ArrayEnumValue(cases={1, 2, 3})
     */
    public string $inlineField;

    /**
     * @ArrayEnumValue(cases=ArrayEnumInput::Cases, allowUnknown=true)
     */
    public string $allowsUnknownField;

}
```

```php
$data = [
	'field' => 1,
	'usesKeysField' => 'second',
	'inlineField' => 3,
	'allowsUnknownField' => 'unknown value',
];
$input = $processor->process($data, ArrayEnumInput::class);
// $input == ArrayEnumInput(field: 1, usesKeysField: 'second', inlineField: 3, allowsUnknownField: null)
```

Parameters:

- `cases`
	- array of accepted values
	- required
- `useKeys`
	- use keys for enumeration instead of values
	- default `false` - values are used for enumeration
- `allowUnknown`
	- for unknown values rule returns null instead of failing
	- default `false`

### float rule

Expects float or int

- int is cast to float

```php
use Orisai\ObjectMapper\MappedObject;
use Orisai\ObjectMapper\Rules\FloatValue;

final class FloatInput implements MappedObject
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
	'anotherField' => '4.2',
];
$input = $processor->process($data, FloatInput::class);
// $input == FloatInput(field: 666.666, anotherField: 4.2)
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
	- e.g. `'10.0'`, `'10'`, `'+10.0'`, `'-10.0'` (commas, spaces etc. are not supported)

### instanceof rule

Expects an instance of specified class or interface

- Use [object rule](#object-rule) to accept any object

```php
use Orisai\ObjectMapper\MappedObject;
use Orisai\ObjectMapper\Rules\InstanceOfValue;
use stdClass;

final class InstanceofInput implements MappedObject
{

    /** @InstanceOfValue(stdClass::class) */
    public stdClass $field;

}
```

```php
$data = [
	'field' => new stdClass(),
];
$input = $processor->process($data, InstanceofInput::class);
// $input == InstanceofInput(field: \stdClass())
```

Parameters:

- `type`
	- type of required instance (class or interface)
	- required
	- e.g. `stdClass::class`

### int rule

Expects int

```php
use Orisai\ObjectMapper\MappedObject;
use Orisai\ObjectMapper\Rules\IntValue;

final class IntInput implements MappedObject
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
$input = $processor->process($data, IntInput::class);
// $input == IntInput(field: 666, anotherField: 42)
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
	- e.g. `'10'`, `'+10'`, `'-10'` (commas, spaces etc. are not supported)

### mixed rule

Expects any value

```php
use Orisai\ObjectMapper\MappedObject;
use Orisai\ObjectMapper\Rules\MixedValue;

final class MixedInput implements MappedObject
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
// $input == MixedInput(field: 'anything')
```

Parameters:

- no parameters

### null rule

Expects null

```php
use Orisai\ObjectMapper\MappedObject;
use Orisai\ObjectMapper\Rules\NullValue;

final class NullInput implements MappedObject
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
$input = $processor->process($data, NullInput::class);
// $input == NullInput(field: null, anotherField: null)
```

Parameters:

- `castEmptyString`
	- accepts any string with only empty characters
	- value is cast to null
	- default `false` - empty strings are not cast
	- e.g. `''`, `'   '`, `"\t"` ,`"\t\n\r""`

When we use `string|null` it may be useful to typecast empty string to null:

```php
use Orisai\ObjectMapper\Rules\AnyOf;
use Orisai\ObjectMapper\Rules\NullValue;
use Orisai\ObjectMapper\Rules\StringValue;
use Orisai\ObjectMapper\MappedObject;

final class NullInput implements MappedObject
{

    /**
     * @AnyOf({
     *     @StringValue(notEmpty=true),
     *     @NullValue(castEmptyString=true),
     * })
     */
    public ?string $field;

}
```

```php
$data = [
	'field' => '',
];
$input = $processor->process($data, NullInput::class);
// $input == NullInput(field: null)
```

### object rule

Expects any object

- Use [instanceof rule](#instanceof-rule) to accept instance of specific type

```php
use Orisai\ObjectMapper\MappedObject;
use Orisai\ObjectMapper\Rules\ObjectValue;

final class ObjectInput implements MappedObject
{

    /** @ObjectValue() */
    public object $field;

}
```

```php
$data = [
	'field' => $anyObject,
];
$input = $processor->process($data, ObjectInput::class);
// $input == ObjectInput(field: $anyObject)
```

Parameters:

- no parameters

### scalar rule

Expects any scalar value - int|float|string|bool

```php
use Orisai\ObjectMapper\MappedObject;
use Orisai\ObjectMapper\Rules\ScalarValue;

final class ScalarInput implements MappedObject
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
$input = $processor->process($data, ScalarInput::class);
// $input == ScalarInput(field: 'any scalar value')
```

Parameters:

- no parameters

### string rule

Expects string

```php
use Orisai\ObjectMapper\MappedObject;
use Orisai\ObjectMapper\Rules\StringValue;

final class StringInput implements MappedObject
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
$input = $processor->process($data, StringInput::class);
// $input == StringInput(field: 'string', anotherField: 'abcdef')
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

### Composed rules

### All of rules - &&

Expects all rules to match

- After first failure is validation terminated, other rules are skipped
- Rules are executed from first to last
- Output value of each rule is input value of the next rule
- Acts as `&&` operator

```php
use Orisai\ObjectMapper\MappedObject;
use Orisai\ObjectMapper\Rules\AllOf;
use Orisai\ObjectMapper\Rules\StringValue;
use Orisai\ObjectMapper\Rules\UrlValue;

final class AllOfInput implements MappedObject
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
$input = $processor->process(['field' => 'https://example.com'], AllOfInput::class);
// $input == AllOfInput(field: 'https://example.com')
```

Parameters:

- `definitions`
	- accepts list of rules by which is the field validated
	- required

### Any of rules - ||

Expects any of rules to match

- Rules are executed from first to last
- Result of first rule which match is used, other rules are skipped
- Acts as `||` operator

```php
use Orisai\ObjectMapper\MappedObject;
use Orisai\ObjectMapper\Rules\AnyOf;
use Orisai\ObjectMapper\Rules\IntValue;
use Orisai\ObjectMapper\Rules\NullValue;
use Orisai\ObjectMapper\Rules\StringValue;

final class AnyOfInput implements MappedObject
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
$input = $processor->process(['field' => 'string'], AnyOfInput::class);
// $input == AnyOfInput(field: 'string')
$input = $processor->process(['field' => 123], AnyOfInput::class);
// $input == AnyOfInput(field: 123)
$input = $processor->process(['field' => null], AnyOfInput::class);
// $input == AnyOfInput(field: null)
```

Parameters:

- `definitions`
	- accepts list of rules by which is the field validated
	- required

### Array of keys and items rule

Expects array

```php
use Orisai\ObjectMapper\MappedObject;
use Orisai\ObjectMapper\Rules\ArrayOf;
use Orisai\ObjectMapper\Rules\IntValue;
use Orisai\ObjectMapper\Rules\MixedValue;
use Orisai\ObjectMapper\Rules\StringValue;

final class ArrayOfInput implements MappedObject
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
    public array $anotherField = ['key1' => 1, 'key2' => 999];

}
```

```php
$data = [
	'field' => ['anything', 1234, true, null],
	'anotherField' => ['key2' => 2, 'key3' => 3],
];
$input = $processor->process($data, ArrayOfInput::class);
// $input == ArrayOfInput(field: ['anything', 1234, true, null], anotherField: ['key1': 1, 'key2': 2, 'key3': 3])
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
use Orisai\ObjectMapper\MappedObject;
use Orisai\ObjectMapper\Rules\ListOf;
use Orisai\ObjectMapper\Rules\StringValue;
use Orisai\ObjectMapper\Rules\MixedValue;

final class ListOfInput implements MappedObject
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
    public array $anotherField = ['default'];

}
```

```php
$data = [
	'field' => ['anything', 1234, true, null],
	'anotherField' => ['one', 'two'],
];
$input = $processor->process($data, ListOfInput::class);
// $input == ListOfInput(field: ['anything', 1234, true, null], anotherField: ['one', 'two', 'default'])
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
use Orisai\ObjectMapper\MappedObject;
use Orisai\ObjectMapper\Rules\BackedEnumValue;

final class BackedEnumInput implements MappedObject
{

	#[BackedEnumValue(ExampleEnum::class)]
	public ExampleEnum $field;

	#[BackedEnumValue(ExampleEnum::class, allowUnknown: true)]
	public ExampleEnum|null $allowsUnknownField;

}

enum ExampleEnum: string
{

	case Foo = 'foo';

}
```

```php
$data = [
	'field' => 'foo',
	'allowsUnknownField' => 'unknown value',
];
$input = $processor->process($data, BackedEnumInput::class);
// $input == BackedEnumInput(field: ExampleEnum::Foo, allowsUnknownField: null)
```

Parameters:

- `class`
	- subclass of `BackedEnum` which should be created
	- required
- `allowUnknown`
	- for unknown values rule returns null instead of failing
	- default `false`

As an alternative to `allowUnknown` we may use `BackedEnum|string`:

```php
use Orisai\ObjectMapper\Rules\AnyOf;
use Orisai\ObjectMapper\Rules\BackedEnumValue;
use Orisai\ObjectMapper\Rules\StringValue;
use Orisai\ObjectMapper\MappedObject;

final class BackedEnumInput implements MappedObject
{

	#[AnyOf([
		new BackedEnumValue(ExampleEnum::class),
		new StringValue(),
	])]
	public ExampleEnum|string $field;

}
```

```php
$data = [
	'field' => 'unknown value',
];
$input = $processor->process($data, BackedEnumInput::class);
// $input == BackedEnumInput(field: 'unknown value')
```

### DateTime rule

Expects datetime as a string or int

- Returns instance of `DateTimeInterface`

```php
use DateTime;
use DateTimeImmutable;
use Orisai\ObjectMapper\MappedObject;
use Orisai\ObjectMapper\Rules\DateTimeValue;

final class DateTimeInput implements MappedObject
{

    /** @DateTimeValue() */
    public DateTimeImmutable $field;

    /** @DateTimeValue(class=DateTime::class, format="timestamp") */
    public DateTime $anotherField;

}
```

```php
$data = [
	'field' => '2013-04-12T16:40:00-04:00',
	'anotherField' => 1365799200,
];
$input = $processor->process($data, DateTimeInput::class);
// $input == DateTimeInput(field: \DateTimeImmutable(), anotherField: \DateTime())
```

Parameters:

- `class`
	- subclass of `DateTimeInterface` which should be created
	- default `DateTimeImmutable`
- `format`
	- expected date-time format
	- default `DateTimeRule::FormatIsoCompat`
		- expects standard ISO 8601 format as defined by
			- `DateTimeInterface::ATOM`
			- and JS ISO format `Y-m-d\TH:i:s.v\Z`
		- e.g. `2013-04-12T16:40:00-04:00`, `2013-04-12T16:40:00.000Z`
	- accepts any of the formats which are [supported by PHP](https://www.php.net/manual/en/datetime.formats.php)
	- to try auto-parse date-time of unknown format, use format `any` (`DateTimeRule::FormatAny`)
	- for timestamp use format `timestamp` (`DateTimeRule::FormatTimestamp`)

### MappedObject rule

Expects `array` or `stdClass` with structure defined by a mapped object

- Returns instance of `MappedObject`
- Objects with all fields being optional are initialized even when no value is sent
  (check [default values](#optional-fields-and-default-values) for further explanation)

```php
use Orisai\ObjectMapper\MappedObject;
use Orisai\ObjectMapper\Rules\MappedObjectValue;
use Orisai\ObjectMapper\Rules\StringValue;

final class MappedObjectInput implements MappedObject
{

    /** @MappedObjectValue(InnerInput::class) */
    public InnerInput $field;

}

class InnerInput implements MappedObject
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
$input = $processor->process($data, MappedObjectInput::class);
// $input == MappedObjectInputInput(field: InnerInput(field: 'string'))
```

Parameters:

- `class`
	- subclass of `MappedObject` which should be created
	- required

## Registering custom rules

To make custom rules available, register them in rule manager

```php
use Orisai\ObjectMapper\Rules\DefaultRuleManager;
use Example\CustomRule;

$ruleManager = new DefaultRuleManager();
$ruleManager->addRule(new CustomRule());
```

## Optional fields and default values

Each field can be made optional by assigning default value to property:

```php
use Orisai\ObjectMapper\MappedObject;
use Orisai\ObjectMapper\Rules\StringValue;

final class OptionalInput implements MappedObject
{

    /** @StringValue() */
    public string $field = 'default value';

}
```

Default values are *never validated by rules* and will not appear in validation errors. We may then assign defaults
which are impossible to send:

```php
use Orisai\ObjectMapper\MappedObject;
use Orisai\ObjectMapper\Rules\StringValue;

final class AnotherOptionalInput implements MappedObject
{

    /** @StringValue() */
    public ?string $field = null;

}
```

Properties without type are null by default in PHP and object mapper can't make difference between implicit and explicit
null on untyped property. To assign null default value to untyped property, use `@DefaultValue()` annotation.

```php
use Orisai\ObjectMapper\MappedObject;
use Orisai\ObjectMapper\Modifiers\DefaultValue;
use Orisai\ObjectMapper\Rules\AnyOf;
use Orisai\ObjectMapper\Rules\MixedValue;
use Orisai\ObjectMapper\Rules\NullValue;
use Orisai\ObjectMapper\Rules\StringValue;

final class NullableVariantsInput implements MappedObject
{

    /**
     * OPTIONAL - has DefaultValue annotation
     *
     * @var mixed
     * @DefaultValue(value=null)
     * @MixedValue()
     */
    public $optionalWithNullDefault;

    /**
     * OPTIONAL - has not null default value
     *
     * @var mixed
     * @MixedValue()
     */
    public $optionalWithNotNullDefault = 'default';

    /**
     * OPTIONAL - typed with null default value
     *
     * @AnyOf({
     *      @StringValue(),
     *      @NullValue(),
     * })
     */
    public ?string $optionalTypedWithNullDefault = null;

    /**
     * REQUIRED - untyped with null default value
     *
     * @var mixed
     * @MixedValue()
     */
    public $requiredWithImplicitNullDefault;

    /**
     * REQUIRED - untyped with null default value
     *          - Identical with $requiredWithImplicitNullDefault - we can't check difference
     *
     * @var mixed
     * @MixedValue()
     */
    public $requiredWithExplicitNullDefault = null;

}
```

Read-only properties may use `#[DefaultValue]` modifier to make field optional (PHP does not allow default value for
read-only properties)

```php
use Orisai\ObjectMapper\MappedObject;
use Orisai\ObjectMapper\Modifiers\DefaultValue;
use Orisai\ObjectMapper\Rules\StringValue;

final class ReadonlyOptionalInput implements MappedObject
{

	#[DefaultValue('default value')]
	#[StringValue]
	public readonly string $field;

}
```

## Allow unknown fields

Make unknown fields allowed instead of throwing exception.

Unknown fields are removed from data and are not available in mapped object.

```php
use Orisai\ObjectMapper\MappedObject;

final class WithUnknownValuesInput implements MappedObject
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
$input = $processor->process($data, WithUnknownValuesInput::class, $options);
// $input == WithUnknownValuesInput()
```

### Ambiguous definitions with unknown fields allowed

If we combine via [any of](#any-of-rules---) two [mapped objects](#mappedobject-rule), where one requires `id`
and `name` and second requires only `id`, then the one with more fields (`id`, `name`) must be listed in any of first.

If the object with `id` only was defined first, and we send `id` and `name`, when unknown fields are allowed,
then `name` would be treated as an unknown field and object with `id` only would be created.

```php
use Orisai\ObjectMapper\Rules\AnyOf;
use Orisai\ObjectMapper\Rules\IntValue;
use Orisai\ObjectMapper\Rules\MappedObjectValue;
use Orisai\ObjectMapper\Rules\StringValue;
use Orisai\ObjectMapper\MappedObject;

final class MainInput implements MappedObject
{

	/**
	 * @var FullInput|IdOnlyInput
	 * @AnyOf({
	 *     @MappedObjectValue(FullInput::class),
	 *     @MappedObjectValue(IdOnlyInput::class),
	 * })
	 */
	public MappedObject $ambiguous;

}

final class FullInput implements MappedObject
{

	/** @IntValue(min=0) */
	public int $id;

	/**
	 * @var non-empty-string
	 * @StringValue(notEmpty=true)
	 */
	public string $name;

}

final class IdOnlyInput implements MappedObject
{

	/** @IntValue(min=0) */
	public int $id;

}
```

## Mapped properties

A property, to be handled by processor during mapping from data fields to properties, must:

- Define a single [rule](#rules)
- Be non-static

Any visibility (public/protected/private) is allowed

### Mapping field names to properties

Keys from input data (fields) are mapped to object properties of the same name, like shown in following example:

```php
use Orisai\ObjectMapper\MappedObject;
use Orisai\ObjectMapper\Rules\MixedValue;

final class DefaultMappingInput implements MappedObject
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
$input = $processor->process($data, DefaultMappingInput::class);
// $input == DefaultMappingInput(field: 'anything')
```

We may change that by defining field name for property:

```php
use Orisai\ObjectMapper\MappedObject;
use Orisai\ObjectMapper\Modifiers\FieldName;
use Orisai\ObjectMapper\Rules\MixedValue;

final class CustomMappingInput implements MappedObject
{

    /**
     * @var mixed
     * @MixedValue()
     * @FieldName("customFieldName")
     */
    public $property;

}
```

We then have to send key from `@FieldName` instead of property name:

```php
$data = [
	'customFieldName' => 'anything',
];
$input = $processor->process($data, CustomMappingInput::class);
// $input == CustomMappingInput(property: 'anything')
```

## Processing modes

Processor requires all fields with no default value to be sent. We may change that and require all fields to be sent or
require no fields at all.

Following mapped object has one required and one optional field (
see [default values](#optional-fields-and-default-values)). By default, you have to send only required field:

```php
use Orisai\ObjectMapper\MappedObject;
use Orisai\ObjectMapper\Rules\BoolValue;

final class ModesExampleInput implements MappedObject
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
// $input == ModesExampleInput(required: true, optional: true)

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

$input = $processor->process($data, ModesExampleInput::class, $options);
// $input == ModesExampleInput(required: true, optional: false)

$input->required; // true
$input->optional; // true
```

### No fields are required

We can make all fields optional. This is useful for partial updates, like PATCH requests in REST APIs. Only changed
fields are sent, and we have to check which ones are available with reflection.

Unlike with default mode, mapped object are not auto-initialized as described
under [mapped object rule](#mappedobject-rule). At least empty array (`[]`) should be sent to initialize them.

Even default values, when not sent, are not initialized - they are unset before assigning sent data.

```php
use Orisai\ObjectMapper\Processing\Options;
use Orisai\ObjectMapper\Processing\RequiredFields;

$data = [];
$options = new Options();
$options->setRequiredFields(RequiredFields::none());

$input = $processor->process($data, ModesExampleInput::class, $options);
// $input == ModesExampleInput(required: __UNSET__, optional: __UNSET__)

(new ReflectionProperty($input, 'required'))->isInitialized($input); // false
$input->required; // Error, property is not set
(new ReflectionProperty($input, 'optional'))->isInitialized($input); // false
$input->optional; // Error, property is not set
```

## Callbacks

Define callbacks before and after mapped objects and their fields:

```php
use Orisai\ObjectMapper\Callbacks\After;
use Orisai\ObjectMapper\Context\FieldContext;
use Orisai\ObjectMapper\MappedObject;
use Orisai\ObjectMapper\Rules\StringValue;

final class WithCallbackInput implements MappedObject
{

	/**
     * @StringValue()
	 * @After("afterField")
	 */
	public string $field;

	private static function afterField(string $value, FieldContext $context): string
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

Callbacks can have any visibility - public, protected or private.

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
use Orisai\ObjectMapper\Callbacks\Before;
use Orisai\ObjectMapper\Callbacks\After;
use Orisai\ObjectMapper\Context\MappedObjectContext;
use Orisai\ObjectMapper\MappedObject;

/**
 * @Before("beforeObject")
 * @After("afterObject")
 */
final class WithMappedObjectCallbacksInput implements MappedObject
{

	/**
     * @param mixed $value
     * @return mixed
     */
	private static function beforeObject($value, MappedObjectContext $context)
	{
		return $value;
	}

	/**
     * @param array<int|string, mixed> $value
     * @return array<int|string, mixed>
     */
	private static function afterObject(array $value, MappedObjectContext $context): array
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
use Orisai\ObjectMapper\Callbacks\Before;
use Orisai\ObjectMapper\Callbacks\After;
use Orisai\ObjectMapper\Context\FieldContext;
use Orisai\ObjectMapper\MappedObject;
use Orisai\ObjectMapper\Rules\StringValue;

final class WithFieldCallbacksInput implements MappedObject
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
	private static function beforeField($value, FieldContext $context)
	{
		return $value;
	}

	private static function afterField(string $value, FieldContext $context): string
	{
		return $value;
	}

}
```

Field callbacks are called only when field is sent. Callback is not invoked for default value.

```php
use Orisai\ObjectMapper\Callbacks\After;
use Orisai\ObjectMapper\Context\FieldContext;
use Orisai\ObjectMapper\MappedObject;
use Orisai\ObjectMapper\Rules\StringValue;

final class WithNotInvokedCallbackInput implements MappedObject
{

	/**
     * @StringValue()
	 * @After("afterField")
	 */
	public string $field = 'default';

	private static function afterField(string $value, FieldContext $context): string
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
use Orisai\ObjectMapper\Callbacks\After;
use Orisai\ObjectMapper\MappedObject;
use Orisai\ObjectMapper\Rules\MixedValue;

final class WithReturningCallbackInput implements MappedObject
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
	private static function afterField($value)
	{
		return $value;
	}

}
```

We may change that by defining `void` or `never` return type:

```php
use Orisai\ObjectMapper\Callbacks\After;
use Orisai\ObjectMapper\Exception\ValueDoesNotMatch;
use Orisai\ObjectMapper\MappedObject;
use Orisai\ObjectMapper\Processing\Value;
use Orisai\ObjectMapper\Rules\StringValue;

final class WithNotReturningCallbackInput implements MappedObject
{

	/**
     * @StringValue()
     * @After("afterRemoved")
     */
	public string $removed;

	private static function afterRemoved(string $value): void
	{
		throw ValueDoesNotMatch::createFromString('Field is removed', Value::of($value));
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

## Dependencies

Mapped object can use services for extended validation in [callbacks](#callbacks). To do so, you have to:

- create a `DependencyInjector` for specific `MappedObject` implementation and set its dependencies
- register injector into `DependencyInjectorManager`
- request injector in `MappedObject` via `RequiresDependencies`

An example, step-by-step implementation is bellow:

Create a `DependencyInjector` for your mapped object

```php
use Orisai\ObjectMapper\MappedObject;
use Orisai\ObjectMapper\Processing\DependencyInjector;

/**
 * @implements DependencyInjector<WithDependenciesInput>
 */
final class WithDependenciesInputInjector implements DependencyInjector
{

	private ExampleService $exampleService;

	public function __construct(ExampleService $exampleService)
	{
		$this->exampleService = $exampleService;
	}

	public function getClass(): string
	{
		return WithDependenciesInput::class;
	}

	/**
	 * @param WithDependenciesInput $object
	 */
	public function inject(MappedObject $object): void
	{
		$object->exampleService = $this->exampleService;
	}

}
```

Register injector to the injector manager

```php
$dependencyInjectorManager->add(new WithDependenciesInputInjector(new ExampleService()));
```


Create mapped object that requires dependencies via `RequiresDependencies`, specifying a `DependencyInjector`

```php
use Orisai\ObjectMapper\Callbacks\After;
use Orisai\ObjectMapper\Exception\ValueDoesNotMatch;
use Orisai\ObjectMapper\MappedObject;
use Orisai\ObjectMapper\Modifiers\RequiresDependencies;
use Orisai\ObjectMapper\Processing\Value;
use Orisai\ObjectMapper\Rules\MixedValue;

/**
 * @RequiresDependencies(injector=WithDependenciesInputInjector::class)
 */
final class WithDependenciesInput implements MappedObject
{

	public ExampleService $service;

	/**
     * @MixedValue()
     * @After("afterField")
     */
	public $field;

	/**
     * @param mixed $value
     * @return mixed
     */
	private function afterField($value)
	{
		if (!$this->service->valueMatchesCriteria($value)) {
			throw ValueDoesNotMatch::createFromString('Value does not match criteria ABC.', Value::of($value))
		}

		return $value;
	}

}
```

Create an instance of mapped object

```php
$input = $processor->process(['field' => 'value'], WithDependenciesInput::class); // WithDependenciesInput
// $input == WithDependenciesInput(field: 'value', service: ExampleService())
```

## Printers

Whole mapped object structure is visualized via [Type](#types) interface. It is used for
the [expected structure](#printing-types) and the [errors in the structure](#printing-errors) being mapped.

### Printing errors

Print errors from an exception thrown during processing

```php
try {
	$user = $processor->process($data, UserInput::class);
} catch (InvalidData $exception) {
	$error = $errorPrinter->printError($exception);

	throw new Exception("Validation failed due to following error:\n$error");
}
```

Print to string

````php
use Orisai\ObjectMapper\Printers\ErrorVisualPrinter;
use Orisai\ObjectMapper\Printers\TypeToStringConverter;

$printer = new ErrorVisualPrinter(new TypeToStringConverter());
````

Print to array

````php
use Orisai\ObjectMapper\Printers\ErrorVisualPrinter;
use Orisai\ObjectMapper\Printers\TypeToArrayConverter;

$printer = new ErrorVisualPrinter(new TypeToArrayConverter());
````

If you only validate part of a bigger structure, you may specify path to the validated node to include it in the error

```php
$error = $errorPrinter->printError($exception, ['path', 'to', 'node']);
```

### Printing types

Print types to get an abstract representation of the expected structure of the send data

```php
use Orisai\ObjectMapper\Context\TypeContext;
use Orisai\ObjectMapper\Processing\Options;
use Orisai\ObjectMapper\Rules\MappedObjectArgs;
use Orisai\ObjectMapper\Rules\MappedObjectRule;

$rule = $ruleManager->getRule(MappedObjectRule::class);
$type = $rule->createType(
	new MappedObjectArgs(UserInput::class),
	new TypeContext($metaLoader, $ruleManager, new Options()),
);
$printedType = $typePrinter->printType($type);
```

Print to string

````php
use Orisai\ObjectMapper\Printers\TypeToStringConverter;
use Orisai\ObjectMapper\Printers\TypeVisualPrinter;

$printer = new TypeVisualPrinter(new TypeToStringConverter());
````

Print to array

````php
use Orisai\ObjectMapper\Printers\TypeToArrayConverter;
use Orisai\ObjectMapper\Printers\TypeVisualPrinter;

$printer = new TypeVisualPrinter(new TypeToArrayConverter());
````

## Metadata validation and preloading

Object metadata (annotations/attributes) are validated and saved when `$processor->process()` is first called. To check
metadata validity in advance, without mapping to an actual object, use `MetaLoader`

```php
$metaLoader->load(ExampleMappedObject::class);
```

To preload all objects from a path, use:

```php
$metaLoader->preloadFromPaths([
	__DIR__ . '/path1',
	__DIR__ . '/path2',
]);
```

You may as well exclude paths

```php
$metaLoader->preloadFromPaths([
	__DIR__ . '/path1',
	__DIR__ . '/path2',
], [
	__DIR__ . '/path1/excluded',
]);
```

## Tracking input values

Track input values of every mapped object in hierarchy, before they were processed by rules.

> Use only for debugging, because this may be memory intensive

```php
use Orisai\ObjectMapper\Processing\Options;

$initialValues = [];

$options = new Options();
$options->setTrackRawValues(true);
$object = $processor->process($initialValues, TrackedInput::class, $options);

$values = $processor->getRawValues($object); // mixed
$values === $initialValues; // true
```

To make it work,following conditions must be met:

- object was mapped by object mapper (no manually created objects)
- it was mapped in current php process (no serialization)
- option is set to enable raw values tracking

## Create mapped object without using object mapper

Object mapper never uses mapped object constructor, all [mapped properties](#mapped-properties) are set directly and
for [dependencies](#dependencies) you can use either properties or setters.

In practice that means, you can create your objects directly without using object mapper, using a constructor:

```php
use Orisai\ObjectMapper\MappedObject;
use Orisai\ObjectMapper\Rules\StringValue;

final class OptionallyMappedInput implements MappedObject
{

	/** @StringValue() */
	public string $field;

	public function __construct(string $field)
	{
		$this->field = $field;
	}

}
```

```php
$mappedInput = $processor->process(['field' => 'string'], OptionallyMappedInput::class); // OptionallyMappedInput
$manuallyCreatedInput = new OptionallyMappedInput('string');
// $mappedInput == $manuallyCreatedInput;
```

Bonus: MappedObject interface is part
of [orisai/object-mapper-contracts](https://github.com/orisai/object-mapper-contracts) package and classes used
in [annotations and attributes](#annotations-and-attributes) and [callbacks](#callbacks) don't have to exist in runtime
for the manual creation to still work. That means your mapped objects containing library can use object mapper as an
optional dependency.

## Types

Types are representation of a valid data structure expected to be sent for data to be successfully validated and mapped
to a mapped object. Types also include any errors that occurred during validation.

They are used in:

- [rules](#rules) - Rules create types and mark them invalid during validation
- [callbacks](#callbacks) - Callbacks may create type or use given types and mark them invalid
- [printers](#printers) - Printers print [types](#printing-types) and [errors](#printing-errors)

### ArrayShapeType

Represents an array shape like `array{key1: string, key2: int}`

```php
use Orisai\ObjectMapper\Types\ArrayShapeType;
use Orisai\ObjectMapper\Types\SimpleValueType;

$type = new ArrayShapeType();
$type->addField('field', new SimpleValueType('string'));
$type->getFields(); // array<int|string, Type>

$type->markInvalid();
$type->isInvalid(); // bool

$type->hasInvalidFields(); // bool
$type->isFieldInvalid('field'); // bool
$type->overwriteInvalidField('field', $exception);
$type->getInvalidFields(); // array<int|string, WithTypeAndValue>

$type->hasErrors() // bool
$type->addError($exception);
$type->getErrors(); // list<WithTypeAndValue>
```

### CompoundType

Represents multiple types combined via `&&` or `||`

```php
use Orisai\ObjectMapper\Types\CompoundType;
use Orisai\ObjectMapper\Types\CompoundTypeOperator;
use Orisai\ObjectMapper\Types\SimpleValueType;

$operator = CompoundTypeOperator::or();
$type = new CompoundType($operator);

$type->getOperator(); // CompoundTypeOperator, $operator

$type->addSubtype(0, new SimpleValueType('int'));
$type->addSubtype(1, new SimpleValueType('string'));
$type->getSubtypes(); // array<int|string, Type>

// Subtype validation was skipped because validation of the other types failed
$type->setSubtypeSkipped(0);
$type->isSubtypeSkipped(0); // bool

$type->getInvalidSubtypes(); // array<int|string, Type>
$type->isSubtypeInvalid(0); // bool
$type->overwriteInvalidSubtype(0, $exception);

$type->isSubtypeValid(0); // bool
```

### EnumType

Represents an enumeration

```php
use Orisai\ObjectMapper\Types\EnumType;

$cases = ['foo', 'bar', 'baz'];
$type = new EnumType($cases);

$type->getCases(); // array<mixed>, $cases
```

### GenericArrayType

Represents arrays like `array<string, int>` and `array<int>` as well as `list<int>`

```php
use Orisai\ObjectMapper\Types\GenericArrayType;
use Orisai\ObjectMapper\Types\SimpleValueType;

$name = 'array';
$keyType = null;
$itemType = new SimpleValueType('string');
$type = new GenericArrayType('array', $keyType, $itemType);

$type->getName(); // string, $name
$type->getKeyType(); // Type|null, $keyType
$type->getItemType(); // Type, $itemType

$type->markInvalid();
$type->isInvalid(); // bool

$type->hasInvalidPairs(); // bool
$type->getInvalidPairs(); // array<int|string, KeyValueErrorPair>
$type->addInvalidKey($key, $keyException);
$type->addInvalidValue($key, $valueException);
$type->addInvalidPair($key, $keyException, $valueException);
```

GenericArrayType is a [ParametrizedType](#parametrizedtype) and shares all of its methods.

### MappedObjectType

Represents a `MappedObject`

```php
use Orisai\ObjectMapper\Types\MappedObjectType;

$class = UserInput::class;
$type = new MappedObjectType($class);

$type->getClass(); // class-string<MappedObject>, $class
```

MappedObjectType is an [ArrayShapeType](#arrayshapetype) and shares all of its methods.

### MessageType

Represents a simple text message

```php
use Orisai\ObjectMapper\Types\MessageType;

$message = 'message';
$type = new MessageType($message);

$type->getMessage(); // string, $message
```

### SimpleValueType

Represents simple value like string, int, user_id etc.

```php
use Orisai\ObjectMapper\Types\SimpleValueType;

$name = 'int';
$type = new SimpleValueType($name);

$type->getName(); // string, $name
```

SimpleValueType is a [ParametrizedType](#parametrizedtype) and shares all of its methods.

### ParametrizedType

Adds parameters to a type, e.g. min and max allowed values to an int.

```php
use Orisai\ObjectMapper\Types\SimpleValueType;

$type = new SimpleValueType('int'); // Or other ParametrizedType

$type->addKeyParameter('unsigned');
$type->addKeyValueParameter('min', 10);
$type->hasParameter('min'); // bool
$type->getParameter('min'); // TypeParameter
$type->getParameters(); // array<int|string, TypeParameter>

$type->markParameterInvalid('min');
$type->markParametersInvalid(['min', 'max']);
$type->hasInvalidParameters(); // bool

$parameter = $type->getParameter('min'); // TypeParameter
$parameter->getKey(); // int|string
$parameter->hasValue(); // bool
$parameter->getValue(); // mixed

$parameter->markInvalid();
$parameter->isInvalid(); // bool
```

Parametrized types are:

- [SimpleValueType](#simplevaluetype)
- [GenericArrayType](#genericarraytype)
