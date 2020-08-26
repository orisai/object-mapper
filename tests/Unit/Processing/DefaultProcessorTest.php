<?php declare(strict_types = 1);

namespace Tests\Orisai\ObjectMapper\Unit\Processing;

use DateTimeImmutable;
use Orisai\Exceptions\Logic\InvalidArgument;
use Orisai\ObjectMapper\Exception\InvalidData;
use Orisai\ObjectMapper\Formatting\ErrorFormatter;
use Orisai\ObjectMapper\Formatting\VisualErrorFormatter;
use Orisai\ObjectMapper\Options;
use Orisai\ObjectMapper\ValueObject;
use stdClass;
use Tests\Orisai\ObjectMapper\Fixtures\AfterClassCallbackCurrentTypeValidationExceptionVO;
use Tests\Orisai\ObjectMapper\Fixtures\AfterClassCallbackNewTypeValidationExceptionVO;
use Tests\Orisai\ObjectMapper\Fixtures\AfterClassCallbackRuleExceptionVO;
use Tests\Orisai\ObjectMapper\Fixtures\BeforeClassCallbackRuleExceptionVO;
use Tests\Orisai\ObjectMapper\Fixtures\CallbacksVO;
use Tests\Orisai\ObjectMapper\Fixtures\DefaultsVO;
use Tests\Orisai\ObjectMapper\Fixtures\EmptyVO;
use Tests\Orisai\ObjectMapper\Fixtures\InitializingVO;
use Tests\Orisai\ObjectMapper\Fixtures\NoDefaultsVO;
use Tests\Orisai\ObjectMapper\Fixtures\PropertiesInitVO;
use Tests\Orisai\ObjectMapper\Fixtures\PropertyCallbacksFailureVO;
use Tests\Orisai\ObjectMapper\Fixtures\StructuresVO;
use Tests\Orisai\ObjectMapper\Fixtures\TransformingVO;
use Tests\Orisai\ObjectMapper\Toolkit\ProcessingTestCase;
use function sprintf;

final class DefaultProcessorTest extends ProcessingTestCase
{

	private ErrorFormatter $formatter;

	protected function setUp(): void
	{
		parent::setUp();
		$this->formatter = new VisualErrorFormatter();
	}

	public function testMissingRequiredValues(): void
	{
		$vo = null;
		$exception = null;
		$data = [];

		try {
			$vo = $this->processor->process($data, NoDefaultsVO::class);
		} catch (InvalidData $exception) {
			// Checked bellow
		}

		self::assertNull($vo);
		self::assertInstanceOf(InvalidData::class, $exception);

		self::markTestSkipped('TODO - all structure values are invalid and complete type must be rendered');
		self::assertSame(
			'string: string
nullableString: string|null
untypedString: string
arrayOfMixed: array<mixed>
manyStructures: array<int(unsigned), structure[
	string: string
	nullableString: string|null
	untypedNullableString: string|null
	untypedNull: null
	arrayOfMixed: array<mixed>
]>',
			$this->formatter->formatError($exception),
		);
	}

	public function testInvalidData(): void
	{
		$vo = null;
		$exception = null;
		$data = 'wtf';

		try {
			$vo = $this->processor->process($data, NoDefaultsVO::class);
		} catch (InvalidData $exception) {
			// Checked bellow
		}

		self::assertNull($vo);
		self::assertInstanceOf(InvalidData::class, $exception);

		self::assertSame(
			'string: string
nullableString: string|null
untypedString: string
arrayOfMixed: array<mixed>
structure: structure[
	string: string
	nullableString: string|null
	untypedNullableString: string|null
	untypedNull: null
	arrayOfMixed: array<mixed>
]
manyStructures: array<int(unsigned), structure[
	string: string
	nullableString: string|null
	untypedNullableString: string|null
	untypedNull: null
	arrayOfMixed: array<mixed>
]>',
			$this->formatter->formatError($exception),
		);
	}

	public function testInvalidArrayItems(): void
	{
		$vo = null;
		$exception = null;
		$data = [
			'string' => 'foo',
			'nullableString' => null,
			'untypedString' => 'foo',
			'arrayOfMixed' => [],
			'manyStructures' => [
				['test' => 'foo'],
				'badKey' => [],
				'anotherBadKey' => ['string' => null],
			],
		];

		try {
			$vo = $this->processor->process($data, NoDefaultsVO::class);
		} catch (InvalidData $exception) {
			// Checked bellow
		}

		self::assertNull($vo);
		self::assertInstanceOf(InvalidData::class, $exception);

		self::markTestSkipped('TODO - badKey and anotherBadKey are completely invalid, complete type (including parameters) must be rendered');
		self::assertSame(
			'manyStructures: array{
	0: structure[
		test: Field is unknown.
	]
	badKey: int(unsigned) => value
	anotherBadKey: int(unsigned) => structure[
		string: string
	]
}',
			$this->formatter->formatError($exception),
		);
	}

	public function testRequiredValues(): void
	{
		$data = [
			'string' => 'foo',
			'nullableString' => null,
			'untypedString' => 'untyped',
			'arrayOfMixed' => [],
			'manyStructures' => [
				[],
				[],
				[],
			],
		];
		$vo = $this->processor->process($data, NoDefaultsVO::class);

		self::assertInstanceOf(NoDefaultsVO::class, $vo);
		self::assertSame('foo', $vo->string);
		self::assertNull($vo->nullableString);
		self::assertSame('untyped', $vo->untypedString);
		self::assertSame([], $vo->arrayOfMixed);
		self::assertCount(3, $vo->manyStructures);

		foreach ($vo->manyStructures as $structure) {
			self::assertInstanceOf(DefaultsVO::class, $structure);
		}
	}

	public function testStructures(): void
	{
		$data = [
			'structureOrArray' => ['valueWhichIsNotInDefaultsVO' => null],
			'anotherStructureOrArray' => ['string' => 'value of property which is in DefaultsVO'],
			'manyStructures' => [
				[ // Not all properties are defined by NoDefaultsVO, should match DefaultsVO
					'string' => 'example',
					'untypedNull' => null,
				],
				[], // Empty should match DefaultsVO
				[ // Not all properties are defined by DefaultsVO, should match NoDefaultsVO
					'string' => 'example',
					'nullableString' => 'example',
					'untypedString' => 'example',
					'arrayOfMixed' => [],
					'manyStructures' => [],
				],
			],
		];
		$vo = $this->processor->process($data, StructuresVO::class);

		self::assertInstanceOf(StructuresVO::class, $vo);
		self::assertInstanceOf(DefaultsVO::class, $vo->structure);
		self::assertIsArray($vo->structureOrArray);
		self::assertSame(['valueWhichIsNotInDefaultsVO' => null], $vo->structureOrArray);
		self::assertInstanceOf(DefaultsVO::class, $vo->anotherStructureOrArray);
		self::assertSame('value of property which is in DefaultsVO', $vo->anotherStructureOrArray->string);
		self::assertCount(3, $vo->manyStructures);
		self::assertInstanceOf(DefaultsVO::class, $vo->manyStructures[0]);
		self::assertInstanceOf(DefaultsVO::class, $vo->manyStructures[1]);
		self::assertInstanceOf(NoDefaultsVO::class, $vo->manyStructures[2]);
	}

	public function testUnknownValues(): void
	{
		$vo = null;
		$exception = null;
		$data = [
			'unknown' => 'Example',
			123 => 'Numeric example',
			'stringg' => 'foo',
		];

		try {
			$vo = $this->processor->process($data, DefaultsVO::class);
		} catch (InvalidData $exception) {
			// Checked bellow
		}

		self::assertNull($vo);
		self::assertInstanceOf(InvalidData::class, $exception);

		self::assertSame(
			'unknown: Field is unknown.
123: Field is unknown.
stringg: Field is unknown, did you mean `string`?',
			$this->formatter->formatError($exception),
		);
	}

	public function testDefaultValues(): void
	{
		$data = [];
		$vo = $this->processor->process($data, DefaultsVO::class);

		self::assertInstanceOf(DefaultsVO::class, $vo);
		self::assertSame('foo', $vo->string);
		self::assertNull($vo->nullableString);
		self::assertNull($vo->untypedNullableString);
		self::assertNull($vo->untypedNull);
		// phpcs:disable Squiz.Arrays.ArrayDeclaration.KeySpecified
		self::assertSame(
			[
				'foo',
				'bar' => 'baz',
			],
			$vo->arrayOfMixed,
		);

		// Defaults are not pre-filled by default
		$processed = $this->processor->processWithoutInitialization($data, DefaultsVO::class);
		self::assertSame([], $processed);

		// Pre-fill defaults
		$options = new Options();
		$options->setPreFillDefaultValues();
		$processed = $this->processor->processWithoutInitialization($data, DefaultsVO::class, $options);
		self::assertSame(
			[
				'string' => 'foo',
				'nullableString' => null,
				'untypedNullableString' => null,
				'untypedNull' => null,
				'arrayOfMixed' => [
					'foo',
					'bar' => 'baz',
				],
			],
			$processed,
		);
		// phpcs:enable
	}

	public function testNoInitialization(): void
	{
		$options = new Options();
		$options->setPreFillDefaultValues();

		$instance = new stdClass();
		$instance->foo = 'bar';

		$data = [
			'datetime' => 'now',
			'instance' => $instance,
			'structure' => [],
		];
		$processedData = $this->processor->processWithoutInitialization($data, InitializingVO::class, $options);

		self::assertSame(
			[
				'datetime' => 'now', // Returns 'now' intentionally instead of value which would be returned by instance serialization
				'instance' => $instance,
				'structure' => [
					'string' => 'foo',
					'nullableString' => null,
					'untypedNullableString' => null,
					'untypedNull' => null,
					'arrayOfMixed' => [
						0 => 'foo',
						'bar' => 'baz',
					],
				],
			],
			$processedData,
		);
	}

	public function testInitialization(): void
	{
		$instance = new stdClass();
		$instance->foo = 'bar';

		$data = [
			'datetime' => '2011-01-01T15:03:01.012345Z',
			'instance' => $instance,
			'structure' => [],
		];
		$vo = $this->processor->process($data, InitializingVO::class);

		self::assertInstanceOf(InitializingVO::class, $vo);
		self::assertInstanceOf(DateTimeImmutable::class, $vo->datetime);
		self::assertSame('2011-01-01T15:03:01.012345', $vo->datetime->format('Y-m-d\TH:i:s.u'));
		self::assertInstanceOf(stdClass::class, $vo->instance);
	}

	public function testTransformation(): void
	{
		$options = new Options();
		$options->setFillRawValues();

		$data = [
			'bool' => 'true',
			'int' => '123',
			'float' => '123,456',
			'stdClassOrNull' => '',
		];
		$vo = $this->processor->process($data, TransformingVO::class, $options);

		self::assertInstanceOf(TransformingVO::class, $vo);
		self::assertTrue($vo->bool);
		self::assertSame(123, $vo->int);
		self::assertSame(123.456, $vo->float);
		self::assertNull($vo->stdClassOrNull);

		self::assertSame($data, $vo->getRawValues());
	}

	public function testCallbacks(): void
	{
		$options = new Options();
		$options->setPreFillDefaultValues();
		$options->setDynamicContext(CallbacksVO::class, [
			CallbacksVO::STRUCTURE_CLASS => DefaultsVO::class,
		]);

		$data = [
			'array' => [
				'foo' => ['bar'],
			],
			'callbackSetValue' => 'givenByUser',
		];

		$processedData = $this->processor->processWithoutInitialization($data, CallbacksVO::class, $options);
		$vo = $this->processor->process($data, CallbacksVO::class, $options);

		// phpcs:disable Squiz.Arrays.ArrayDeclaration.KeySpecified
		self::assertSame(
			[
				'array' => [
					'foo' => ['bar'],
					'beforeClassCallback' => [false],
					'afterArrayProcessingCallback' => [false],
					'afterClassCallback' => [false],
				],
				'callbackSetValue' => 'givenByConstructor',
				'structure' => [
					'string' => 'foo',
					'nullableString' => null,
					'untypedNullableString' => null,
					'untypedNull' => null,
					'arrayOfMixed' => [
						'foo',
						'bar' => 'baz',
					],
				],
				'overridenDefaultValue' => 'overriddenValue',
				'requiredValue' => 'overriddenValue',
				'immutableDefaultValue' => 'defaultValue_immutable',
			],
			$processedData,
		);
		// phpcs:enable

		self::assertSame(
			[
				'foo' => ['bar'],
				'beforeClassCallback' => [true],
				'afterArrayInitializationCallback' => [true],
				'afterClassCallback' => [true],
			],
			$vo->array,
		);
		self::assertInstanceOf(DefaultsVO::class, $vo->structure);
		self::assertSame('overriddenValue', $vo->overridenDefaultValue);
		self::assertSame('defaultValue_immutable', $vo->immutableDefaultValue);
		self::assertSame('overriddenValue', $vo->requiredValue);
		self::assertSame('givenByConstructor', $vo->callbackSetValue);
	}

	public function testPropertyCallbacksFailure(): void
	{
		$vo = null;
		$exception = null;
		$data = [];

		try {
			$vo = $this->processor->process($data, PropertyCallbacksFailureVO::class);
		} catch (InvalidData $exception) {
			// Checked bellow
		}

		self::assertNull($vo);
		self::assertInstanceOf(InvalidData::class, $exception);

		self::assertSame(
			'neverValidated: Check before validation failed, field was never validated
validationFailed: string',
			$this->formatter->formatError($exception),
		);
	}

	public function testBeforeClassCallbackRuleExceptiom(): void
	{
		$vo = null;
		$exception = null;
		$data = [];

		try {
			$vo = $this->processor->process($data, BeforeClassCallbackRuleExceptionVO::class);
		} catch (InvalidData $exception) {
			// Checked bellow
		}

		self::assertNull($vo);
		self::assertInstanceOf(InvalidData::class, $exception);

		self::assertSame(
			'Error before class',
			$this->formatter->formatError($exception),
		);
	}

	public function testAfterClassCallbackRuleException(): void
	{
		$vo = null;
		$exception = null;
		$data = [
			'string' => 'string',
		];

		try {
			$vo = $this->processor->process($data, AfterClassCallbackRuleExceptionVO::class);
		} catch (InvalidData $exception) {
			// Checked bellow
		}

		self::assertNull($vo);
		self::assertInstanceOf(InvalidData::class, $exception);

		self::assertSame(
			'Error after class',
			$this->formatter->formatError($exception),
		);
	}

	public function testAfterClassCallbackCurrentTypeValidationException(): void
	{
		$vo = null;
		$exception = null;
		$data = [
			'string' => 'string',
		];

		try {
			$vo = $this->processor->process($data, AfterClassCallbackCurrentTypeValidationExceptionVO::class);
		} catch (InvalidData $exception) {
			// Checked bellow
		}

		self::assertNull($vo);
		self::assertInstanceOf(InvalidData::class, $exception);

		self::assertSame(
			'string: string',
			$this->formatter->formatError($exception),
		);
	}

	public function testAfterClassCallbackNewTypeValidationException(): void
	{
		$vo = null;
		$exception = null;
		$data = [
			'string' => 'string',
		];

		try {
			$vo = $this->processor->process($data, AfterClassCallbackNewTypeValidationExceptionVO::class);
		} catch (InvalidData $exception) {
			// Checked bellow
		}

		self::assertNull($vo);
		self::assertInstanceOf(InvalidData::class, $exception);

		self::assertSame(
			'structure[
	test
]',
			$this->formatter->formatError($exception),
		);
	}

	public function testRequiredNonDefaultFields(): void
	{
		$options = new Options();
		$options->setRequiredFields($options::REQUIRE_NON_DEFAULT);

		$vo = $this->processor->process([
			'required' => null,
		], PropertiesInitVO::class, $options);

		self::assertTrue(isset($vo->required));
		self::assertTrue(isset($vo->optional));
		self::assertTrue(isset($vo->structure));

		self::assertNull($vo->required);
		self::assertNull($vo->optional);
		self::assertInstanceOf(EmptyVO::class, $vo->structure);
	}

	public function testRequireAllFields(): void
	{
		$options = new Options();
		$options->setRequiredFields($options::REQUIRE_ALL);

		$vo = $this->processor->process([
			'required' => null,
			'optional' => null,
			'structure' => [],
		], PropertiesInitVO::class, $options);

		self::assertTrue(isset($vo->required));
		self::assertTrue(isset($vo->optional));
		self::assertTrue(isset($vo->structure));

		self::assertNull($vo->required);
		self::assertNull($vo->optional);
		self::assertInstanceOf(EmptyVO::class, $vo->structure);
	}

	public function testRequireAllFieldsError(): void
	{
		$options = new Options();
		$options->setRequiredFields($options::REQUIRE_ALL);

		$vo = null;
		$exception = null;
		$data = [];

		try {
			$vo = $this->processor->process($data, DefaultsVO::class, $options);
		} catch (InvalidData $exception) {
			// Checked bellow
		}

		self::assertNull($vo);
		self::assertInstanceOf(InvalidData::class, $exception);

		self::markTestSkipped('TODO - all structure values are invalid and complete type must be rendered');
		self::assertSame(
			'string: string
nullableString: string|null
untypedNullableString: string|null
untypedNull: null
arrayOfMixed: array<mixed>',
			$this->formatter->formatError($exception),
		);
	}

	public function testRequireNoneFields(): void
	{
		$options = new Options();
		$options->setRequiredFields($options::REQUIRE_NONE);

		$vo = $this->processor->process([], PropertiesInitVO::class, $options);

		self::assertFalse(isset($vo->required));
		self::assertFalse(isset($vo->optional));
		self::assertFalse(isset($vo->structure));

		$vo = $this->processor->process([
			'required' => null,
			'optional' => null,
			'structure' => [],
		], PropertiesInitVO::class, $options);

		self::assertTrue(isset($vo->required));
		self::assertTrue(isset($vo->optional));
		self::assertTrue(isset($vo->structure));

		self::assertNull($vo->required);
		self::assertNull($vo->optional);
		self::assertInstanceOf(EmptyVO::class, $vo->structure);
	}

	public function testNotAClass(): void
	{
		$this->expectException(InvalidArgument::class);
		$this->expectExceptionMessage('Class "foo" does not exist');

		$this->processor->process([], 'foo');
	}

	public function testNotAValueObject(): void
	{
		$this->expectException(InvalidArgument::class);
		$this->expectExceptionMessage(sprintf(
			'Class "%s" should be non-abstract subclass of "%s"',
			stdClass::class,
			ValueObject::class,
		));

		$this->processor->process([], stdClass::class);
	}

}
