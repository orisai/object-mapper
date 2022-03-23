# Object Mapper

Raw data mapping to validated objects

## Content

TODO

## Intro

TODO

## Setup

TODO
- basic
- nette

## Processing

TODO
- processor
	- filling defaults
	- running rules for all fields
	- invoking callbacks
	- filling object
- handling exceptions
	- orisai/exceptions
- default values and their requirement
	- explicit/implicit null
	- other defaults

## MappedObject

TODO
- mapped fields (properties)
	- each need own rule
	- terminology - field/property difference
- value object and structure
	- terminology
- rules/callbacks/docs inheritance
- magic

## Options

TODO
- required values
- prefill values (no initialization mode)
- raw values
- dynamic contexts

## Rules

TODO

#### AllOfRule

Expects all rules to match
- After first failure is validation terminated, other rules are skipped
- Rules are executed from first to last

```php
use Orisai\ObjectMapper\Attributes\Expect\AllOf;
use Orisai\ObjectMapper\Attributes\Expect\IntValue;
use Orisai\ObjectMapper\Attributes\Expect\NullValue;
use Orisai\ObjectMapper\Attributes\Expect\StringValue;
use Orisai\ObjectMapper\MappedObject;

class VO extends MappedObject
{

    /**
     * @var string|int|null
     * @AllOf(
     *      @StringValue(),
     *      @IntValue(),
     *      @NullValue(),
     * )
     */
    public $field;

}
```

Parameters:
- `rules`
	- accepts list of rules by which is the field validated
	- required

#### AnyOfRule

Expects any of rules to match
- Rules are executed from first to last
- Result of first rule which match is used, other rules are skipped

```php
use Orisai\ObjectMapper\Attributes\Expect\AnyOf;
use Orisai\ObjectMapper\Attributes\Expect\IntValue;
use Orisai\ObjectMapper\Attributes\Expect\NullValue;
use Orisai\ObjectMapper\Attributes\Expect\StringValue;
use Orisai\ObjectMapper\MappedObject;

class VO extends MappedObject
{

    /**
     * @var string|int|null
     * @AnyOf(
     *      @StringValue(),
     *      @IntValue(),
     *      @NullValue(),
     * )
     */
    public $field;

}
```

Parameters:
- `rules`
	- accepts list of rules by which is the field validated
	- required

#### ArrayOfRule

Expects array

```php
use Orisai\ObjectMapper\Attributes\Expect\ArrayOf;
use Orisai\ObjectMapper\Attributes\Expect\IntValue;
use Orisai\ObjectMapper\Attributes\Expect\MixedValue;
use Orisai\ObjectMapper\Attributes\Expect\StringValue;
use Orisai\ObjectMapper\MappedObject;

class VO extends MappedObject
{

    /**
     * @var array<mixed>
     * @ArrayOf(
     *      @MixedValue()
     * )
     */
    public array $field;

    /**
     * @var array<string, int>
     * @ArrayOf(
     *      keyType=@StringValue(),
     *      itemType=@IntValue(),
     * )
     */
    public array $anotherField;

}
```

Parameters:
- `itemType`
	- accepts rule which is used to validate items
	- required
- `keyType`
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
- `mergeDefaults`
	- merge default value into array after it is validated
	- default `false` - default is not merged

#### BoolRule

Expects bool

```php
use Orisai\ObjectMapper\Attributes\Expect\BoolValue;
use Orisai\ObjectMapper\MappedObject;

class VO extends MappedObject
{

    /** @BoolValue() */
    public bool $field;

}
```

Parameters:
- `castBoolLike`
	- accepts also `0` (int), `1` (int), `'true'` (string, any case), `'false'` (string, any case)
	- value is casted to respective bool value
	- default `false` - bool like are not casted

#### DateTimeRule

Expects datetime as a string or int
- Returns instance of `DateTimeImmutable`

```php
use DateTimeImmutable;
use Orisai\ObjectMapper\Attributes\Expect\DateTime;
use Orisai\ObjectMapper\MappedObject;

class VO extends MappedObject
{

    /** @DateTime() */
    public DateTimeImmutable $field;

}
```

Parameters:
- `format`
	- expected date time format
	- recommended is `DateTimeInterface::ATOM`
	- default `null` - tries to parse datetime automatically via `new DateTimeImmutable`
	- TODO - custom formáty - timestamp, ISO8601-fixed
	- TODO - link na formáty v php dokumentaci

#### FloatRule

Expects float or int
- Int is casted to float

```php
use Orisai\ObjectMapper\Attributes\Expect\FloatValue;
use Orisai\ObjectMapper\MappedObject;

class VO extends MappedObject
{

    /** @FloatValue() */
    public float $field;

}
```

Parameters:
- `castFloatLike`
	- accepts also numeric strings (float and int)
	- value is casted to respective float value
	- default `false` - float like are not casted
	- TODO - zdokumentovat formát
- `unsigned`
	- accepts only positive numbers
	- default `true` - only positive numbers are accepted
- `min`
	- minimal accepted value
	- default `null` - no limit
	- e.g. `10.0`
- `max`
	- maximal accepted value
	- default `null` - no limit
	- e.g. `100.0`

#### InstanceRule

Expects an instance of class
- Use ObjectRule to accept any object

```php
use Orisai\ObjectMapper\Attributes\Expect\InstanceValue;
use Orisai\ObjectMapper\MappedObject;
use stdClass;

class VO extends MappedObject
{

    /** @InstanceValue(stdClass::class) */
    public stdClass $field;

}
```

Parameters:
- `type`
	- type of required instance
	- required
	- e.g. `stdClass::class`

#### IntRule

Expects int

```php
use Orisai\ObjectMapper\Attributes\Expect\IntValue;
use Orisai\ObjectMapper\MappedObject;

class VO extends MappedObject
{

    /** @IntValue() */
    public int $field;

}
```

Parameters:
- `castIntLike`
	- accepts also numeric strings (int)
	- value is casted to respective int value
	- default `false` - int like are not casted
	- TODO - zdokumentovat formát
- `unsigned`
	- accepts only positive numbers
	- default `true` - only positive numbers are accepted
- `min`
	- minimal accepted value
	- default `null` - no limit
	- e.g. `10`
- `max`
	- maximal accepted value
	- default `null` - no limit
	- e.g. `100`

#### ListOfRule

Expects list
- All keys must be incremental integers

```php
use Orisai\ObjectMapper\Attributes\Expect\ListOf;
use Orisai\ObjectMapper\Attributes\Expect\MixedValue;
use Orisai\ObjectMapper\MappedObject;

class VO extends MappedObject
{

    /**
     * @var array<int, mixed>
     * @ListOf(
     *      @MixedValue()
     * )
     */
    public array $field;

}
```

Parameters:
- `itemType`
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
- `mergeDefaults`
	- merge default value into array after it is validated
	- default `false` - default is not merged

#### MixedRule

Expects any value

```php
use Orisai\ObjectMapper\Attributes\Expect\MixedValue;
use Orisai\ObjectMapper\MappedObject;

class VO extends MappedObject
{

    /**
     * @var mixed
     * @MixedValue()
     */
    public $field;

}
```

Parameters:
- no parameters

#### NullRule

Expects null

```php
use Orisai\ObjectMapper\Attributes\Expect\NullValue;
use Orisai\ObjectMapper\MappedObject;

class VO extends MappedObject
{

    /**
     * @var null
     * @NullValue()
     */
    public $field;

}
```

Parameters:
- `castEmptyString`
	- accepts any string with only empty characters
	- value is casted to null
	- default `false` - empty strings are not casted
	- e.g. `''`, `"\t"` ,`"\t\n\r""`

#### ObjectRule

Expects any object
- Use InstanceRule to accept instance of specific type

```php
use Orisai\ObjectMapper\Attributes\Expect\ObjectValue;
use Orisai\ObjectMapper\MappedObject;

class VO extends MappedObject
{

    /** @ObjectValue() */
    public object $field;

}
```

Parameters:
- no parameters

#### ScalarRule

Expects any scalar value - int|float|string|bool

```php
use Orisai\ObjectMapper\Attributes\Expect\ScalarValue;
use Orisai\ObjectMapper\MappedObject;

class VO extends MappedObject
{

    /**
     * @var int|float|string|bool
     * @ScalarValue()
     */
    public $field;

}
```

Parameters:
- no parameters

#### StringRule

Expects string

```php
use Orisai\ObjectMapper\Attributes\Expect\StringValue;
use Orisai\ObjectMapper\MappedObject;

class VO extends MappedObject
{

    /** @StringValue() */
    public string $field;

}
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
	- string must not contain *only* empty characters
	- default `false` - empty strings are allowed
	- e.g. `''`, `"\t"` ,`"\t\n\r""`
- `pattern`
	- regex pattern which must match
	- default `null` - no validation by pattern
	- e.g. `/[\s\S]/`

#### StructureRule

Expects array with predefined structure
- Returns instance of `MappedObject`
- Works even if value was not sent at all, so value objects which fields all have default values could be initialized
  and only errors for required fields are displayed.
	- TODO - pouze pokud není v rámci složeného typu a pokud se používá defaultní mód

```php
use Orisai\ObjectMapper\Attributes\Expect\MixedValue;
use Orisai\ObjectMapper\Attributes\Expect\Structure;
use Orisai\ObjectMapper\MappedObject;

class VO extends MappedObject
{

    /** @Structure(InnerVO::class) */
    public InnerVO $field;

}

class InnerVO extends MappedObject
{

    /**
     * @var mixed
     * @MixedValue()
     */
    public $field;

}
```

Parameters:
- `type`
	- subclass of `MappedObject` which should be created
	- required

#### ValueEnumRule

Expects any of values from given list

```php
use Orisai\ObjectMapper\Attributes\Expect\ValueEnum;
use Orisai\ObjectMapper\MappedObject;

class VO extends MappedObject
{

    public const ENUM_VALUES = [
        'first',
        'second',
        'third'
    ];

    /**
     * @var mixed
     * @ValueEnum(VO::ENUM_VALUES)
     */
    public $field;

}
```

Parameters:
- `useKeys`
	- use keys for enumeration instead of values
	- default `false` - values are used for enumeration

#### UrlRule

Expects valid url address

```php
use Orisai\ObjectMapper\Attributes\Expect\Url;
use Orisai\ObjectMapper\MappedObject;

class VO extends MappedObject
{

    /** @Url() */
    public string $field;

}
```

Parameters:
- no parameters

### Add rules
- TODO - rule manager, mapping

### Create own rules
- TODO

## Callbacks

TODO
- before
	- add and remove fields
- after
	- add and remove fields
		- too late, skipped and missing fields are already handled
		- TODO - maybe could throw exception
- validations, errors
- field names are always used
- contexts

### Before and after validation

TODO

### Class and property

TODO
- v jaké fázi se volají
- s jakými daty
- jaký je dostupný kontext
- property
- nevolá se, když není dostupná hodnota
- když je hodnota výchozí, tak ??

### Return types

Callbacks may or may not return value.
In PHP it is not possible to differentiate between `return;` and `return null;` when value is assigned to variable
so if callback returns value it must do it every time.
Object mapper infers whether value is returned from method return type.
With `void` value is not expected, with every other type including no type value is expected.

```php
use Orisai\ObjectMapper\Attributes\Callback\Before;
use Orisai\ObjectMapper\Attributes\Expect\StringValue;
use Orisai\ObjectMapper\MappedObject;

class DTO extends MappedObject
{

	/**
	 * @StringValue()
	 * @Before("beforeField1")
	 */
	public string $field1;

	/**
	 * @StringValue()
	 * @Before("beforeField2")
	 */
	public string $field2;

	/**
	 * @param mixed $value
	 */
	public static function beforeField1($value): void
	{
		// Can't return value, has void return type
	}

	/**
	 * @param mixed $value
	 * @return mixed
	 */
	public static function beforeField2($value)
	{
		// Must return value, has non-void or none return type
		return $value;
	}

}
```

### Services and instance callbacks

Non-static methods can be used in case services are needed in callbacks.
Object mapper initializes value object and injects services via `ObjectCreator`.

> Do not change mapped properties values directly, `Processor` will always override them after "after class" callback

```php
use Orisai\ObjectMapper\Attributes\Callback\Before;
use Orisai\ObjectMapper\MappedObject;

/**
 * @Before("beforeClass")
 */
class DTO extends MappedObject
{

	private MyService $service;

	public function __construct(MyService $service)
	{
		$this->service = $service;
	}

	/**
	 * @param array<mixed> $data
	 */
	public function beforeClass(array $data): void
	{
		// Do whatever you want with service
	}

}
```

#### With basic setup

Basic setup uses `DefaultObjectCreator` which has no ability to create services. DIC-specific implementation is required.

#### With Nette

Setup with Nette DI uses `NetteObjectCreator`.
It creates value objects via DIC and allows you to request autowired services in constructor.

> Option `di > export > types` (`DIExtension`) is required to be enabled for this functionality.

### Runtime

Callbacks can be executed depending on whether objects are initialized (`$processor->process(...);`)
or not (`$processor->processWithoutInitialization(...)`).

It may be useful for object initialization in after callbacks.
Also in case object-initializing rules are used then in after callbacks are instances already created.

Set one of the `CallbackRuntime`s
- `CallbackRuntime::ALWAYS` - default, run callback for both `process()` and `processWithoutInitialization()`
- `CallbackRuntime::INITIALIZATION` - run callback for `process()`
- `CallbackRuntime::PROCESSING` - run callback for `processWithoutInitialization()`

```php
use Orisai\ObjectMapper\Attributes\Callback\After;
use Orisai\ObjectMapper\Attributes\Expect\StringValue;
use Orisai\ObjectMapper\Callbacks\CallbackRuntime;
use Orisai\ObjectMapper\MappedObject;

class DTO extends MappedObject
{

	/**
	 * @StringValue()
	 * @After(method="beforeField", runtime=CallbackRuntime::INITIALIZATION)
	 */
	public UnicodeString $field;

	public static function afterField(string $value): UnicodeString
	{
		return new UnicodeString($value);
	}

}
```

Callbacks can also differentiate behavior internally based on context

```php
use Orisai\ObjectMapper\Attributes\Callback\After;
use Orisai\ObjectMapper\Attributes\Expect\StringValue;
use Orisai\ObjectMapper\Callbacks\CallbackRuntime;
use Orisai\ObjectMapper\Context\FieldContext;
use Orisai\ObjectMapper\MappedObject;

class DTO extends MappedObject
{

	/**
	 * @StringValue()
	 * @After(method="afterField", runtime=CallbackRuntime::ALWAYS)
	 */
	public UnicodeString $field;

	/**
	 * @return string|UnicodeString
	 */
	public static function afterField(string $value, FieldContext $context)
	{
		if ($context->isInitializeObjects()) {
			return new UnicodeString($value);
		}

		return $value;
	}

}
```

## Formatters

Formatters are classes used to transform value objects metadata and validation errors into various formats

### Errors

TODO

### Documentation

TODO

## Skipped initialization

TODO - process without initialization

## Skipped properties

TODO
- callbacks
	- not available in after class callback
	- before class callback is okay
	- class callbacks are not invoked after skipped property initialization
	- property callbacks are invoked when property is initialized

## Documentation

TODO
- annotations
- formatters
	- defaults
	- docs
	- type

## Metadata

TODO
- meta sources
	- annotations
- annotations
	- create own
