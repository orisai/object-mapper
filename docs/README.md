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
	- [Dependencies](#dependencies)
	- [Context](#callback-context)
- [Annotations and attributes](#annotations-and-attributes)
- [Object creator](#object-creator)
- [Create without constructor](#create-without-constructor)
- [Metadata validation and preloading](#metadata-validation-and-preloading)

## Setup

Install with [Composer](https://getcomposer.org)

```sh
composer require orisai/object-mapper
```

Configure processor:

```php
use Orisai\ObjectMapper\Attributes\AnnotationsMetaSource;
use Orisai\ObjectMapper\Attributes\AttributesMetaSource;
use Orisai\ObjectMapper\Meta\ArrayMetaCache;
use Orisai\ObjectMapper\Meta\DefaultMetaResolverFactory;
use Orisai\ObjectMapper\Meta\DefaultMetaSourceManager;
use Orisai\ObjectMapper\Meta\MetaLoader;
use Orisai\ObjectMapper\Processing\DefaultObjectCreator;
use Orisai\ObjectMapper\Processing\DefaultProcessor;
use Orisai\ObjectMapper\ReflectionMeta\Collector\AnnotationsCollector;
use Orisai\ObjectMapper\ReflectionMeta\Collector\AttributesCollector;
use Orisai\ObjectMapper\Rules\DefaultRuleManager;

$sourceManager = new DefaultMetaSourceManager();
$sourceManager->addSource(new AnnotationsMetaSource()); // For doctrine/annotations
$sourceManager->addSource(new AttributesMetaSource()); // For PHP 8 attributes
$ruleManager = new DefaultRuleManager();
$objectCreator = new DefaultObjectCreator();
$cache = new ArrayMetaCache();
$resolverFactory = new DefaultMetaResolverFactory($ruleManager, $objectCreator);
$metaLoader = new MetaLoader($cache, $sourceManager, $resolverFactory);

$processor = new DefaultProcessor(
	$metaLoader,
	$ruleManager,
	$objectCreator,
);
```

Or, if you use Nette, check [orisai/nette-object-mapper](https://github.com/orisai/nette-object-mapper) for installation.

## Quick start

After you have finished [setup](#setup), define a mapped object:

```php
use Orisai\ObjectMapper\MappedObject;
use Orisai\ObjectMapper\Attributes\Expect\StringValue;

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
use Orisai\ObjectMapper\Attributes\Expect\ArrayEnumValue;
use Orisai\ObjectMapper\MappedObject;

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
    public string $anotherField;

    /**
     * @ArrayEnumValue(cases={1, 2, 3})
     */
    public string $inlineValueField;

}
```

```php
$data = [
	'field' => 1,
	'anotherField' => 'first',
];
$input = $processor->process($data, ArrayEnumInput::class);
// $input == ArrayEnumInput(field: 1, anotherField: 'first')
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

### instanceof rule

Expects an instance of specified class or interface

- Use [object rule](#object-rule) to accept any object

```php
use Orisai\ObjectMapper\Attributes\Expect\InstanceOfValue;
use Orisai\ObjectMapper\MappedObject;
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
use Orisai\ObjectMapper\Attributes\Expect\IntValue;
use Orisai\ObjectMapper\MappedObject;

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

### mixed rule

Expects any value

```php
use Orisai\ObjectMapper\Attributes\Expect\MixedValue;
use Orisai\ObjectMapper\MappedObject;

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
use Orisai\ObjectMapper\Attributes\Expect\NullValue;
use Orisai\ObjectMapper\MappedObject;

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
use Orisai\ObjectMapper\Attributes\Expect\AnyOf;
use Orisai\ObjectMapper\Attributes\Expect\NullValue;
use Orisai\ObjectMapper\Attributes\Expect\StringValue;
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
use Orisai\ObjectMapper\Attributes\Expect\ObjectValue;
use Orisai\ObjectMapper\MappedObject;

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
use Orisai\ObjectMapper\Attributes\Expect\ScalarValue;
use Orisai\ObjectMapper\MappedObject;

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
use Orisai\ObjectMapper\Attributes\Expect\StringValue;
use Orisai\ObjectMapper\MappedObject;

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
use Orisai\ObjectMapper\Attributes\Expect\UrlValue;
use Orisai\ObjectMapper\MappedObject;

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
use Orisai\ObjectMapper\Attributes\Expect\ListOf;
use Orisai\ObjectMapper\Attributes\Expect\StringValue;
use Orisai\ObjectMapper\Attributes\Expect\MixedValue;
use Orisai\ObjectMapper\MappedObject;

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
use Orisai\ObjectMapper\Attributes\Expect\BackedEnumValue;
use Orisai\ObjectMapper\MappedObject;

final class BackedEnumInput implements MappedObject
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
$input = $processor->process($data, BackedEnumInput::class);
// $input == BackedEnumInput(field: ExampleEnum::Foo, anotherField: null)
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
use Orisai\ObjectMapper\Attributes\Expect\AnyOf;
use Orisai\ObjectMapper\Attributes\Expect\BackedEnumValue;
use Orisai\ObjectMapper\Attributes\Expect\StringValue;
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
use Orisai\ObjectMapper\Attributes\Expect\DateTimeValue;
use Orisai\ObjectMapper\MappedObject;

final class DateTimeInput implements MappedObject
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
$input = $processor->process($data, DateTimeInput::class);
// $input == DateTimeInput(field: \DateTimeImmutable(), anotherField: \DateTime())
```

Parameters:

- `type`
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

- `type`
	- subclass of `MappedObject` which should be created
	- required

### URL rule

Expects valid url address

```php
use Orisai\ObjectMapper\Attributes\Expect\UrlValue;
use Orisai\ObjectMapper\MappedObject;

final class UrlInput implements MappedObject
{

    /** @UrlValue() */
    public string $field;

}
```

```php
$data = [
	'field' => 'https://example.com',
];
$input = $processor->process($data, UrlInput::class);
// $input == UrlInput(field: 'https://example.com')
```

Parameters:

- no parameters

## Optional fields and default values

Each field can be made optional by assigning default value to property:

```php
use Orisai\ObjectMapper\Attributes\Expect\StringValue;
use Orisai\ObjectMapper\MappedObject;

final class OptionalInput implements MappedObject
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

final class AnotherOptionalInput implements MappedObject
{

    /** @StringValue() */
    public ?string $field = null;

}
```

Properties without type are null by default in PHP and object mapper can't make difference between implicit and explicit
null on untyped property. To assign null default value to untyped property, use `@DefaultValue()` annotation.

```php
use Orisai\ObjectMapper\Attributes\Expect\AnyOf;
use Orisai\ObjectMapper\Attributes\Expect\MixedValue;
use Orisai\ObjectMapper\Attributes\Expect\NullValue;
use Orisai\ObjectMapper\Attributes\Expect\StringValue;
use Orisai\ObjectMapper\Attributes\Modifiers\DefaultValue;
use Orisai\ObjectMapper\MappedObject;

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
use Orisai\ObjectMapper\Attributes\Expect\StringValue;
use Orisai\ObjectMapper\Attributes\Modifiers\DefaultValue;
use Orisai\ObjectMapper\MappedObject;

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
use Orisai\ObjectMapper\Attributes\Expect\AnyOf;
use Orisai\ObjectMapper\Attributes\Expect\IntValue;
use Orisai\ObjectMapper\Attributes\Expect\MappedObjectValue;
use Orisai\ObjectMapper\Attributes\Expect\StringValue;
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
use Orisai\ObjectMapper\Attributes\Expect\MixedValue;
use Orisai\ObjectMapper\MappedObject;

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
use Orisai\ObjectMapper\Attributes\Expect\MixedValue;
use Orisai\ObjectMapper\Attributes\Modifiers\FieldName;
use Orisai\ObjectMapper\MappedObject;

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
use Orisai\ObjectMapper\Attributes\Expect\BoolValue;
use Orisai\ObjectMapper\MappedObject;

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
use Orisai\ObjectMapper\MappedObject;
use Orisai\ObjectMapper\Attributes\Callbacks\After;
use Orisai\ObjectMapper\Attributes\Expect\StringValue;
use Orisai\ObjectMapper\Context\FieldContext;

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
use Orisai\ObjectMapper\MappedObject;
use Orisai\ObjectMapper\Attributes\Callbacks\Before;
use Orisai\ObjectMapper\Attributes\Callbacks\After;
use Orisai\ObjectMapper\Context\MappedObjectContext;

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
use Orisai\ObjectMapper\MappedObject;
use Orisai\ObjectMapper\Attributes\Callbacks\Before;
use Orisai\ObjectMapper\Attributes\Callbacks\After;
use Orisai\ObjectMapper\Attributes\Expect\StringValue;
use Orisai\ObjectMapper\Context\FieldContext;

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
use Orisai\ObjectMapper\MappedObject;
use Orisai\ObjectMapper\Attributes\Callbacks\After;
use Orisai\ObjectMapper\Attributes\Expect\StringValue;
use Orisai\ObjectMapper\Context\FieldContext;

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
use Orisai\ObjectMapper\MappedObject;
use Orisai\ObjectMapper\Attributes\Callbacks\After;
use Orisai\ObjectMapper\Attributes\Expect\MixedValue;

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
use Orisai\ObjectMapper\MappedObject;
use Orisai\ObjectMapper\Attributes\Callbacks\After;
use Orisai\ObjectMapper\Attributes\Expect\StringValue;
use Orisai\ObjectMapper\Exception\ValueDoesNotMatch;
use Orisai\ObjectMapper\Types\Value;

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

### Dependencies

> To use this feature, check [object creator](#object-creator)

Mapped objects can request dependencies in constructor for extended validation in callbacks:

```php
use Orisai\ObjectMapper\MappedObject;
use Orisai\ObjectMapper\Attributes\Callbacks\After;
use Orisai\ObjectMapper\Attributes\Expect\MixedValue;
use Orisai\ObjectMapper\Exception\ValueDoesNotMatch;
use Orisai\ObjectMapper\Types\Value;

final class WithComplexCallbackInput implements MappedObject
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
	private function afterField($value)
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

## Create without constructor

Processor uses [object creator](#object-creator) to inject dependencies for [callbacks](#callbacks) via mapped object
constructor. This makes object creation viable only via `$processor->process()`. To create a mapped object manually,
without object mapper, use `CreateWithoutConstructor` modifier.

```php
use Orisai\ObjectMapper\Attributes\Expect\StringValue;
use Orisai\ObjectMapper\Attributes\Modifiers\CreateWithoutConstructor;
use Orisai\ObjectMapper\MappedObject;

/**
 * @CreateWithoutConstructor()
 */
final class ConstructorUsingVO implements MappedObject
{

	/** @StringValue() */
	public string $string;

	public function __construct(string $string)
	{
		$this->string = $string;
	}

}
```

With this modifier, both manual and object mapper approach work

```php
$vo = new ConstructorUsingVO('string');
$vo = $processor->process(['string' => 'string'], ConstructorUsingVO::class); // ConstructorUsingVO
```

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
