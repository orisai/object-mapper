<?php declare(strict_types = 1);

namespace Tests\Orisai\ObjectMapper\Unit\Processing;

use DateTimeImmutable;
use DateTimeInterface;
use Orisai\Exceptions\Logic\InvalidState;
use Orisai\ObjectMapper\Exception\InvalidData;
use Orisai\ObjectMapper\MappedObject;
use Orisai\ObjectMapper\Printers\ErrorPrinter;
use Orisai\ObjectMapper\Printers\ErrorVisualPrinter;
use Orisai\ObjectMapper\Printers\TypeToStringConverter;
use Orisai\ObjectMapper\Processing\Options;
use Orisai\ObjectMapper\Processing\RequiredFields;
use ReflectionProperty;
use RuntimeException;
use stdClass;
use Tests\Orisai\ObjectMapper\Doubles\Callbacks\AfterClassCallbackCurrentTypeInvalidDataVO;
use Tests\Orisai\ObjectMapper\Doubles\Callbacks\AfterClassCallbackNewTypeInvalidDataVO;
use Tests\Orisai\ObjectMapper\Doubles\Callbacks\AfterClassCallbackValueDoesNotMatchVO;
use Tests\Orisai\ObjectMapper\Doubles\Callbacks\BeforeClassCallbackMixedValueVO;
use Tests\Orisai\ObjectMapper\Doubles\Callbacks\BeforeClassCallbackValueDoesNotMatchVO;
use Tests\Orisai\ObjectMapper\Doubles\Callbacks\CallbackOverrideChildVO;
use Tests\Orisai\ObjectMapper\Doubles\Callbacks\CallbacksVO;
use Tests\Orisai\ObjectMapper\Doubles\Callbacks\CallbacksVoContext;
use Tests\Orisai\ObjectMapper\Doubles\Callbacks\InvalidateFieldBeforeClassVO;
use Tests\Orisai\ObjectMapper\Doubles\Callbacks\ObjectInitializingVO;
use Tests\Orisai\ObjectMapper\Doubles\Callbacks\ObjectInitializingVoPhp81;
use Tests\Orisai\ObjectMapper\Doubles\Callbacks\PropertyCallbacksFailureVO;
use Tests\Orisai\ObjectMapper\Doubles\Circular\CircularAVO;
use Tests\Orisai\ObjectMapper\Doubles\Circular\CircularBVO;
use Tests\Orisai\ObjectMapper\Doubles\Circular\CircularCVO;
use Tests\Orisai\ObjectMapper\Doubles\Circular\SelfReferenceVO;
use Tests\Orisai\ObjectMapper\Doubles\DefaultsVO;
use Tests\Orisai\ObjectMapper\Doubles\Dependencies\DependentBaseVoInjector;
use Tests\Orisai\ObjectMapper\Doubles\Dependencies\DependentChildVO;
use Tests\Orisai\ObjectMapper\Doubles\Dependencies\DependentChildVoInjector1;
use Tests\Orisai\ObjectMapper\Doubles\Dependencies\DependentChildVoInjector2;
use Tests\Orisai\ObjectMapper\Doubles\EmptyVO;
use Tests\Orisai\ObjectMapper\Doubles\FieldNames\ChildFieldVO;
use Tests\Orisai\ObjectMapper\Doubles\FieldNames\FieldNamesVO;
use Tests\Orisai\ObjectMapper\Doubles\ForbiddenConstructorVO;
use Tests\Orisai\ObjectMapper\Doubles\Inheritance\CallbacksVisibilityVO;
use Tests\Orisai\ObjectMapper\Doubles\Inheritance\ChildVO;
use Tests\Orisai\ObjectMapper\Doubles\Inheritance\InterfaceUsingVO;
use Tests\Orisai\ObjectMapper\Doubles\Inheritance\PropertiesVisibilityVO;
use Tests\Orisai\ObjectMapper\Doubles\Inheritance\TraitAlias1\TraitAlias1VO;
use Tests\Orisai\ObjectMapper\Doubles\Inheritance\TraitAlias2\TraitAlias2VO;
use Tests\Orisai\ObjectMapper\Doubles\Inheritance\TraitAlias3\TraitAlias3VO;
use Tests\Orisai\ObjectMapper\Doubles\Inheritance\TraitAlias4\TraitAlias4VO;
use Tests\Orisai\ObjectMapper\Doubles\Inheritance\TraitCallback\TraitCallbackVO;
use Tests\Orisai\ObjectMapper\Doubles\Inheritance\TraitInsideTrait\TraitInsideTraitVO;
use Tests\Orisai\ObjectMapper\Doubles\Inheritance\TraitInsteadOf1\TraitInstead1OfVO;
use Tests\Orisai\ObjectMapper\Doubles\InitializingVO;
use Tests\Orisai\ObjectMapper\Doubles\InternalClassExtendingVO;
use Tests\Orisai\ObjectMapper\Doubles\NoDefaultsVO;
use Tests\Orisai\ObjectMapper\Doubles\PhpVersionSpecific\AttributesVO;
use Tests\Orisai\ObjectMapper\Doubles\PhpVersionSpecific\ConstructorPromotedVO;
use Tests\Orisai\ObjectMapper\Doubles\PhpVersionSpecific\DefaultsOverrideVO;
use Tests\Orisai\ObjectMapper\Doubles\PhpVersionSpecific\NewInInitializersVO;
use Tests\Orisai\ObjectMapper\Doubles\PhpVersionSpecific\ObjectDefaultVO;
use Tests\Orisai\ObjectMapper\Doubles\PhpVersionSpecific\ReadonlyClassVO;
use Tests\Orisai\ObjectMapper\Doubles\PhpVersionSpecific\ReadonlyPropertiesVO;
use Tests\Orisai\ObjectMapper\Doubles\PhpVersionSpecific\UntypedVO;
use Tests\Orisai\ObjectMapper\Doubles\PropertiesInitVO;
use Tests\Orisai\ObjectMapper\Doubles\Skipped\SkippedFieldsVO;
use Tests\Orisai\ObjectMapper\Doubles\StructuresVO;
use Tests\Orisai\ObjectMapper\Doubles\TransformingVO;
use Tests\Orisai\ObjectMapper\Toolkit\ProcessingTestCase;
use const PHP_VERSION_ID;

final class DefaultProcessorTest extends ProcessingTestCase
{

	private ErrorPrinter $printer;

	protected function setUp(): void
	{
		parent::setUp();
		$this->printer = new ErrorVisualPrinter(new TypeToStringConverter());
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
nullableString: string||null
arrayOfMixed: array<mixed>
manyStructures: array<int(unsigned), shape{
	string: string
	nullableString: string||null
	arrayOfMixed: array<mixed>
}>',
			$this->printer->printError($exception),
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
nullableString: string||null
arrayOfMixed: array<mixed>
structure: shape{
	string: string
	nullableString: string||null
	arrayOfMixed: array<mixed>
}
manyStructures: array<int, shape{
	string: string
	nullableString: string||null
	arrayOfMixed: array<mixed>
}>',
			$this->printer->printError($exception),
		);
	}

	public function testInvalidArrayItems(): void
	{
		$vo = null;
		$exception = null;
		$data = [
			'string' => 'foo',
			'nullableString' => null,
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

		self::markTestSkipped(
			'TODO - badKey and anotherBadKey are completely invalid, complete type (including parameters) must be rendered',
		);
		self::assertSame(
			'manyStructures: array[
	0: shape{
		test: Field is unknown.
	}
	badKey: int(unsigned) => value
	anotherBadKey: int(unsigned) => shape{
		string: string
	}
}',
			$this->printer->printError($exception),
		);
	}

	public function testRequiredValues(): void
	{
		$data = [
			'string' => 'foo',
			'nullableString' => null,
			'arrayOfMixed' => [],
			'structure' => [],
			'manyStructures' => [
				[],
				[],
				[],
			],
		];
		$vo = $this->processor->process($data, NoDefaultsVO::class);

		self::assertEquals(
			$vo,
			new NoDefaultsVO(
				'foo',
				null,
				[],
				new DefaultsVO(),
				[
					new DefaultsVO(),
					new DefaultsVO(),
					new DefaultsVO(),
				],
			),
		);
	}

	public function testStructures(): void
	{
		$data = [
			'structure' => [],
			'structureOrArray' => ['valueWhichIsNotInDefaultsVO' => null],
			'anotherStructureOrArray' => ['string' => 'value of property which is in DefaultsVO'],
			'manyStructures' => [
				[ // Not all properties are defined by NoDefaultsVO, should match DefaultsVO
					'string' => 'example',
				],
				[], // Empty should match DefaultsVO
				[ // Not all properties are defined by DefaultsVO, should match NoDefaultsVO
					'string' => 'example',
					'nullableString' => 'example',
					'arrayOfMixed' => [],
					'structure' => [],
					'manyStructures' => [],
				],
			],
		];
		$vo = $this->processor->process($data, StructuresVO::class);

		self::assertEquals(
			$vo,
			new StructuresVO(
				new DefaultsVO(),
				['valueWhichIsNotInDefaultsVO' => null],
				new DefaultsVO('value of property which is in DefaultsVO'),
				[
					new DefaultsVO('example'),
					new DefaultsVO(),
					new NoDefaultsVO('example', 'example', [], new DefaultsVO(), []),
				],
			),
		);
	}

	public function testUnknownValues(): void
	{
		$vo = null;
		$exception = null;
		$data = [
			'unknown' => 'Example',
			123 => 'Numeric example',
		];

		try {
			$vo = $this->processor->process($data, EmptyVO::class);
		} catch (InvalidData $exception) {
			// Checked bellow
		}

		self::assertNull($vo);
		self::assertInstanceOf(InvalidData::class, $exception);

		self::assertSame(
			<<<'MSG'
unknown: Field is unknown.
123: Field is unknown.
MSG,
			$this->printer->printError($exception),
		);
	}

	public function testUnknownValuesDidYouMean(): void
	{
		$vo = null;
		$exception = null;
		$data = [
			'nulableString' => null,
			'nullableString' => null,
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
			<<<'MSG'
nulableString: Field is unknown.
stringg: Field is unknown, did you mean 'string'?
MSG,
			$this->printer->printError($exception),
		);
	}

	public function testUnknownValuesAllowed(): void
	{
		$options = new Options();
		$options->setAllowUnknownFields();

		$vo = null;
		$exception = null;
		try {
			$vo = $this->processor->process(['unknown' => true], EmptyVO::class, $options);
		} catch (InvalidData $exception) {
			// Checked bellow
		}

		self::assertNull($exception);
		self::assertEquals($vo, new EmptyVO());
	}

	public function testDefaultValues(): void
	{
		$data = [];
		$vo = $this->processor->process($data, DefaultsVO::class);

		self::assertEquals(
			$vo,
			new DefaultsVO(
				'foo',
				null,
				[
					0 => 'foo',
					'bar' => 'baz',
				],
			),
		);

		// Defaults are not pre-filled by default
		$processed = $this->processor->processWithoutMapping($data, DefaultsVO::class);
		self::assertSame([], $processed);

		// Pre-fill defaults
		$options = new Options();
		$options->setPrefillDefaultValues();
		$processed = $this->processor->processWithoutMapping($data, DefaultsVO::class, $options);
		self::assertSame(
			[
				'string' => 'foo',
				'nullableString' => null,
				'arrayOfMixed' => [
					0 => 'foo',
					'bar' => 'baz',
				],
			],
			$processed,
		);
	}

	public function testUntyped(): void
	{
		$data = [
			'nullableString' => null,
			'null' => null,
		];
		$vo = $this->processor->process($data, UntypedVO::class);

		self::assertEquals(
			$vo,
			new UntypedVO(null, null),
		);
		self::assertSame(
			$data,
			$this->processor->processWithoutMapping($data, UntypedVO::class),
		);

		$data = [
			'nullableString' => 'c',
			'null' => null,
			'nullableStringWithDefault' => 'a',
			'nullWithDefault' => null,
		];
		$vo = $this->processor->process($data, UntypedVO::class);

		self::assertEquals(
			$vo,
			new UntypedVO('c', null, 'a', null),
		);
		self::assertSame(
			$data,
			$this->processor->processWithoutMapping($data, UntypedVO::class),
		);

		$exception = null;
		try {
			$this->processor->process(null, UntypedVO::class);
		} catch (InvalidData $exception) {
			// Handled bellow
		}

		self::assertNotNull($exception);
		self::assertSame(
			<<<'MSG'
nullableString: string||null
null: null
nullableStringWithDefault: string||null
nullWithDefault: null
MSG,
			$this->printer->printError($exception),
		);
	}

	public function testPropertiesVisibility(): void
	{
		$data = [
			'public' => 'public',
			'protected' => 'protected',
			'private' => 'private',
		];

		$vo = $this->processor->process($data, PropertiesVisibilityVO::class);

		self::assertEquals(
			$vo,
			new PropertiesVisibilityVO('public', 'protected', 'private'),
		);
		self::assertSame('public', $vo->public);
		self::assertSame('protected', $vo->getProtected());
		self::assertSame('private', $vo->getPrivate());
	}

	public function testNoInitialization(): void
	{
		$options = new Options();
		$options->setPrefillDefaultValues();

		$instance = new stdClass();
		$instance->foo = 'bar';

		$data = [
			'datetime' => '1990-12-31T12:34:56+00:00',
			'instance' => $instance,
			'structure' => [],
		];
		$processedData = $this->processor->processWithoutMapping($data, InitializingVO::class, $options);

		self::assertSame(
			[
				// Returns raw value intentionally instead of value which would be returned by instance serialization
				'datetime' => '1990-12-31T12:34:56+00:00',
				'instance' => $instance,
				'structure' => [
					'string' => 'foo',
					'nullableString' => null,
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
			'datetime' => '1990-12-31T12:34:56+00:00',
			'instance' => $instance,
			'structure' => [],
		];
		$vo = $this->processor->process($data, InitializingVO::class);

		$dateTime = DateTimeImmutable::createFromFormat(DateTimeInterface::ATOM, '1990-12-31T12:34:56+00:00');
		self::assertNotFalse($dateTime);

		self::assertEquals(
			$vo,
			new InitializingVO(
				$dateTime,
				$instance,
				new DefaultsVO(),
			),
		);
		self::assertSame('1990-12-31T12:34:56+00:00', $vo->datetime->format(DateTimeInterface::ATOM));
		self::assertSame($instance, $vo->instance);
	}

	public function testTransformation(): void
	{
		$options = new Options();
		$options->setTrackRawValues();

		$data = [
			'bool' => 'true',
			'int' => '123',
			'float' => '123.456',
			'stdClassOrNull' => '',
		];
		$vo = $this->processor->process($data, TransformingVO::class, $options);

		self::assertEquals(
			$vo,
			new TransformingVO(true, 123, 123.456, null),
		);
		self::assertSame($data, $this->processor->getRawValues($vo));
	}

	public function testSelfReference(): void
	{
		$data = [
			'selfOrNull' => [
				'selfOrNull' => null,
				'another' => 'string',
			],
			'another' => 'string',
		];

		$vo = $this->processor->process($data, SelfReferenceVO::class);

		self::assertEquals(
			$vo,
			new SelfReferenceVO(
				new SelfReferenceVO(null, 'string'),
				'string',
			),
		);
		self::assertInstanceOf(SelfReferenceVO::class, $vo->selfOrNull);
		self::assertNull($vo->selfOrNull->selfOrNull);
	}

	public function testSelfReferenceFail(): void
	{
		$vo = null;
		$exception = null;
		$data = [
			'selfOrNull' => 'string',
			'another' => 'string',
		];

		try {
			$vo = $this->processor->process($data, SelfReferenceVO::class);
		} catch (InvalidData $exception) {
			// Checked bellow
		}

		self::assertNull($vo);
		self::assertInstanceOf(InvalidData::class, $exception);

		self::assertSame(
			<<<'MSG'
selfOrNull: shape{
	selfOrNull: shape{}||null
	another: string
}||null
MSG,
			$this->printer->printError($exception),
		);
	}

	public function testCircularReference(): void
	{
		$data = [
			'b' => [
				'c' => [
					'as' => [
						[
							'b' => ['c' => null],
						],
						[
							'b' => [
								'c' => ['as' => []],
							],
						],
					],
				],
			],
		];

		$a = $this->processor->process($data, CircularAVO::class);

		self::assertEquals(
			$a,
			new CircularAVO(
				new CircularBVO(
					new CircularCVO(
						[
							new CircularAVO(
								new CircularBVO(null),
							),
							new CircularAVO(
								new CircularBVO(
									new CircularCVO([]),
								),
							),
						],
					),
				),
			),
		);

		$c = $a->b->c;
		self::assertInstanceOf(CircularCVO::class, $c);

		$as = $c->as;
		self::assertCount(2, $as);

		$a1 = $as[0];
		self::assertNull($a1->b->c);

		$a2 = $as[1];
		self::assertNotNull($a2->b->c);
		self::assertSame([], $a2->b->c->as);
	}

	public function testCircularReferenceFail(): void
	{
		$vo = null;
		$exception = null;
		$data = null;

		try {
			$vo = $this->processor->process($data, CircularAVO::class);
		} catch (InvalidData $exception) {
			// Checked bellow
		}

		self::assertNull($vo);
		self::assertInstanceOf(InvalidData::class, $exception);

		self::assertSame(
			<<<'MSG'
b: shape{
	c: shape{
		as: list<int(continuous), shape{}>
	}||null
}
stringOrNull: string||null
MSG,
			$this->printer->printError($exception),
		);
	}

	public function testCallbacks(): void
	{
		$options = new Options();
		$options->setPrefillDefaultValues();
		$options->addDynamicContext(new CallbacksVoContext(DefaultsVO::class));

		$data = [
			'array' => [
				'foo' => ['bar'],
			],
			'callbackSetValue' => 'givenByUser',
		];

		$processedData = $this->processor->processWithoutMapping($data, CallbacksVO::class, $options);
		$vo = $this->processor->process($data, CallbacksVO::class, $options);

		self::assertSame(
			[
				'array' => [
					'foo' => ['bar'],
					'beforeClassCallback' => [false],
					'afterArrayProcessingCallback' => [false],
					'afterClassCallback' => [false],
				],
				'callbackSetValue' => 'givenByBeforeCallback',
				'structure' => [
					'string' => 'foo',
					'nullableString' => null,
					'arrayOfMixed' => [
						0 => 'foo',
						'bar' => 'baz',
					],
				],
				'overriddenDefaultValue' => 'overriddenValue',
				'requiredValue' => 'overriddenValue',
				'immutableDefaultValue' => 'defaultValue_immutable',
			],
			$processedData,
		);

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
		self::assertSame('overriddenValue', $vo->overriddenDefaultValue);
		self::assertSame('defaultValue_immutable', $vo->immutableDefaultValue);
		self::assertSame('overriddenValue', $vo->requiredValue);
		self::assertSame('givenByBeforeCallback', $vo->callbackSetValue);
	}

	public function testCallbacksVisibility(): void
	{
		$data = [
			'public' => 'a',
			'protected' => 'b',
			'private' => 'c',
			'publicStatic' => 'd',
			'protectedStatic' => 'e',
			'privateStatic' => 'f',
		];

		$vo = $this->processor->process($data, CallbacksVisibilityVO::class);

		self::assertEquals(
			$vo,
			new CallbacksVisibilityVO(
				'a-public',
				'b-protected',
				'c-private',
				'd-public-static',
				'e-protected-static',
				'f-private-static',
			),
		);
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
			$this->printer->printError($exception),
		);
	}

	public function testBeforeClassCallbackMixedValue(): void
	{
		$options = new Options();
		$options->setTrackRawValues();

		$vo = null;
		$exception = null;

		try {
			$vo = $this->processor->process(false, BeforeClassCallbackMixedValueVO::class, $options);
		} catch (InvalidData $exception) {
			// Checked bellow
		}

		self::assertNull($vo);
		self::assertInstanceOf(InvalidData::class, $exception);

		self::assertSame(
			'',
			$this->printer->printError($exception),
		);
		$vo = $this->processor->process(true, BeforeClassCallbackMixedValueVO::class, $options);
		self::assertTrue($this->processor->getRawValues($vo));
	}

	public function testBeforeClassCallbackRuleException(): void
	{
		$vo = null;
		$exception = null;
		$data = [];

		try {
			$vo = $this->processor->process($data, BeforeClassCallbackValueDoesNotMatchVO::class);
		} catch (InvalidData $exception) {
			// Checked bellow
		}

		self::assertNull($vo);
		self::assertInstanceOf(InvalidData::class, $exception);

		self::assertSame(
			'Error before class',
			$this->printer->printError($exception),
		);
	}

	public function testBeforeClassCallbackInvalidatesSentField(): void
	{
		$vo = null;
		$exception = null;
		$data = [
			'string' => 'foo',
			'alsoString' => 123,
		];

		try {
			$vo = $this->processor->process($data, InvalidateFieldBeforeClassVO::class);
		} catch (InvalidData $exception) {
			// Checked bellow
		}

		self::assertNull($vo);
		self::assertInstanceOf(InvalidData::class, $exception);

		self::assertSame(
			<<<'MSG'
string: invalidated in before callback
alsoString: string
MSG,
			$this->printer->printError($exception),
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
			$vo = $this->processor->process($data, AfterClassCallbackValueDoesNotMatchVO::class);
		} catch (InvalidData $exception) {
			// Checked bellow
		}

		self::assertNull($vo);
		self::assertInstanceOf(InvalidData::class, $exception);

		self::assertSame(
			'Error after class',
			$this->printer->printError($exception),
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
			$vo = $this->processor->process($data, AfterClassCallbackCurrentTypeInvalidDataVO::class);
		} catch (InvalidData $exception) {
			// Checked bellow
		}

		self::assertNull($vo);
		self::assertInstanceOf(InvalidData::class, $exception);

		self::assertSame(
			'string: string',
			$this->printer->printError($exception),
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
			$vo = $this->processor->process($data, AfterClassCallbackNewTypeInvalidDataVO::class);
		} catch (InvalidData $exception) {
			// Checked bellow
		}

		self::assertNull($vo);
		self::assertInstanceOf(InvalidData::class, $exception);

		self::assertSame(
			'shape{
	test
}',
			$this->printer->printError($exception),
		);
	}

	public function testCallbackMethodOverride(): void
	{
		$data = [
			'field' => 'value',
		];

		$vo = $this->processor->process($data, CallbackOverrideChildVO::class);

		self::assertEquals(
			$vo,
			new CallbackOverrideChildVO('value-parent-child-parentStatic-childStatic'),
		);
	}

	public function testRequiredNonDefaultFields(): void
	{
		$options = new Options();
		$options->setRequiredFields(RequiredFields::nonDefault());

		$vo = $this->processor->process([
			'structure' => [],
			'required' => null,
		], PropertiesInitVO::class, $options);

		self::assertTrue($this->isInitialized($vo, 'structure'));
		self::assertTrue($this->isInitialized($vo, 'required'));
		self::assertTrue($this->isInitialized($vo, 'optional'));

		self::assertEquals(
			$vo,
			new PropertiesInitVO(new EmptyVO(), null),
		);
	}

	public function testObjectInitialization(): void
	{
		$vo = $this->processor->process([], ObjectInitializingVO::class);

		self::assertEquals(
			$vo,
			new ObjectInitializingVO(new DefaultsVO()),
		);
	}

	public function testObjectInitializationPhp81(): void
	{
		if (PHP_VERSION_ID < 8_01_00) {
			self::markTestSkipped('New in initializers is supported since PHP 8.1');
		}

		$vo = $this->processor->process([], ObjectInitializingVoPhp81::class);

		self::assertEquals(
			$vo,
			new ObjectInitializingVoPhp81(new DefaultsVO()),
		);
	}

	public function testConstructorIsNotUsed(): void
	{
		$data = [
			'field' => 'foo',
		];
		$vo = $this->processor->process($data, ForbiddenConstructorVO::class);

		self::assertSame('foo', $vo->field);

		$this->expectException(RuntimeException::class);
		new ForbiddenConstructorVO();
	}

	public function testRequireAllFields(): void
	{
		$options = new Options();
		$options->setRequiredFields(RequiredFields::all());

		$vo = $this->processor->process([
			'structure' => [],
			'required' => null,
			'optional' => null,
		], PropertiesInitVO::class, $options);

		self::assertTrue($this->isInitialized($vo, 'structure'));
		self::assertTrue($this->isInitialized($vo, 'required'));
		self::assertTrue($this->isInitialized($vo, 'optional'));

		self::assertEquals(
			$vo,
			new PropertiesInitVO(new EmptyVO(), null, null),
		);
	}

	public function testRequireAllFieldsError(): void
	{
		$options = new Options();
		$options->setRequiredFields(RequiredFields::all());

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
nullableString: string||null
arrayOfMixed: array<mixed>',
			$this->printer->printError($exception),
		);
	}

	public function testRequireNoneFields(): void
	{
		$options = new Options();
		$options->setRequiredFields(RequiredFields::none());

		$vo = $this->processor->process([], PropertiesInitVO::class, $options);

		self::assertFalse($this->isInitialized($vo, 'structure'));
		self::assertFalse($this->isInitialized($vo, 'required'));
		self::assertFalse($this->isInitialized($vo, 'optional'));

		$vo = $this->processor->process([
			'structure' => [],
			'required' => null,
			'optional' => null,
		], PropertiesInitVO::class, $options);

		self::assertTrue($this->isInitialized($vo, 'structure'));
		self::assertTrue($this->isInitialized($vo, 'required'));
		self::assertTrue($this->isInitialized($vo, 'optional'));

		self::assertEquals(
			$vo,
			new PropertiesInitVO(new EmptyVO(), null, null),
		);
	}

	/**
	 * @runInSeparateProcess
	 */
	public function testDependencies(): void
	{
		$manager = $this->dependencies->dependencyInjectorManager;
		$manager->addInjector(new DependentBaseVoInjector(new stdClass()));
		$manager->addInjector(new DependentChildVoInjector1('string'));
		$manager->addInjector(new DependentChildVoInjector2(123));

		$vo = $this->processor->process([], DependentChildVO::class);

		self::assertEquals(
			new DependentChildVO(
				new stdClass(),
				'string',
				123,
			),
			$vo,
		);
	}

	public function testMappedFieldNames(): void
	{
		$vo = $this->processor->process([
			'original' => 'original',
			'field' => 'property',
			123 => 'integer',
			'swap1' => 'swap2',
			'swap2' => 'swap1',
		], FieldNamesVO::class);

		self::assertSame('original', $vo->original);
		self::assertSame('property', $vo->property);
		self::assertSame('integer', $vo->integer);
		self::assertSame('swap1', $vo->swap1);
		self::assertSame('swap2', $vo->swap2);
	}

	public function testMappedFieldNamesWithInheritance(): void
	{
		$vo = $this->processor->process([
			'property' => 'parent',
			'renamedProperty' => 'child',
		], ChildFieldVO::class);

		self::assertSame('parent', $vo->getParentProperty());
		self::assertSame('child', $vo->getChildProperty());
	}

	public function testSimpleInheritance(): void
	{
		$vo = $this->processor->process([
			'parentPrivate' => 'a1',
			'parentProtected' => 'a2',
			'parentPublic' => 'a3',
			'childPrivate' => 'b1',
			'childProtected' => 'b2',
			'childPublic' => 'b3',
			'childTraitPrivate' => 'c1',
			'childTraitProtected' => 'c2',
			'childTraitPublic' => 'c3',
		], ChildVO::class);

		self::assertSame('a1-parent-parentStatic', $vo->getParentPrivate());
		self::assertSame('a2-parent-parentStatic', $vo->getParentProtected());
		self::assertSame('a3-parent-parentStatic', $vo->parentPublic);
		self::assertSame('b1-child-childStatic', $vo->getChildPrivate());
		self::assertSame('b2-child-childStatic', $vo->getChildProtected());
		self::assertSame('b3-child-childStatic', $vo->childPublic);
		self::assertSame('c1-childTrait-childTraitStatic', $vo->getChildTraitPrivate());
		self::assertSame('c2-childTrait-childTraitStatic', $vo->getChildTraitProtected());
		self::assertSame('c3-childTrait-childTraitStatic', $vo->childTraitPublic);
	}

	public function testTraitInsteadof1(): void
	{
		require_once __DIR__ . '/../../Doubles/Inheritance/trait-instead-of-1.php';

		$vo = $this->processor->process([
			'string' => 'value',
		], TraitInstead1OfVO::class);

		self::assertSame('value-b', $vo->string);
	}

	public function testTraitInsteadof2(): void
	{
		require_once __DIR__ . '/../../Doubles/Inheritance/trait-instead-of-2.php';

		$vo = $this->processor->process([
			'string' => 'value',
		], TraitInstead1OfVO::class);

		self::assertSame('value-b', $vo->string);
	}

	public function testTraitAlias1(): void
	{
		require_once __DIR__ . '/../../Doubles/Inheritance/trait-alias-1.php';

		$vo = $this->processor->process([
			'string' => 'value',
		], TraitAlias1VO::class);

		self::assertSame('value-a', $vo->string);
	}

	public function testTraitAlias2(): void
	{
		require_once __DIR__ . '/../../Doubles/Inheritance/trait-alias-2.php';

		$vo = $this->processor->process([
			'string' => 'value',
		], TraitAlias2VO::class);

		self::assertSame('value-a', $vo->string);
	}

	public function testTraitAlias3(): void
	{
		require_once __DIR__ . '/../../Doubles/Inheritance/trait-alias-3.php';

		$vo = $this->processor->process([
			'string' => 'value',
		], TraitAlias3VO::class);

		self::assertSame('value-a', $vo->string);
	}

	public function testTraitAlias4(): void
	{
		require_once __DIR__ . '/../../Doubles/Inheritance/trait-alias-4.php';

		$vo = $this->processor->process([
			'string' => 'value',
		], TraitAlias4VO::class);

		self::assertSame('value-a', $vo->string);
	}

	public function testTraitInsideTrait(): void
	{
		require_once __DIR__ . '/../../Doubles/Inheritance/trait-inside-trait.php';

		$vo = $this->processor->process([
			'string' => 'value',
		], TraitInsideTraitVO::class);

		self::assertSame('value-private-protected-public', $vo->string);
	}

	public function testInterfaceCallback(): void
	{
		$vo = $this->processor->process([
			'a' => 'input',
			'b' => 'input',
		], InterfaceUsingVO::class);

		self::assertSame('input', $vo->a);
		self::assertSame('callback', $vo->b);
	}

	public function testTraitCallback(): void
	{
		require_once __DIR__ . '/../../Doubles/Inheritance/trait-callback.php';

		$vo = $this->processor->process([
			'string' => 'value',
		], TraitCallbackVO::class);

		self::assertSame('A::before-value-A::after', $vo->string);
	}

	public function testSkipped(): void
	{
		$vo = $this->processor->process([
			'required' => 'required',
			'requiredSkipped' => 'requiredSkipped',
		], SkippedFieldsVO::class);

		self::assertSame('required', $vo->required);
		self::assertSame('optional', $vo->optional);
		self::assertFalse($this->isInitialized($vo, 'requiredSkipped'));
		self::assertFalse($this->isInitialized($vo, 'optionalSkipped'));

		$this->processor->processSkippedFields([
			'requiredSkipped',
			'optionalSkipped',
		], $vo);

		self::assertSame('requiredSkipped', $vo->requiredSkipped);
		self::assertSame('optionalSkipped', $vo->optionalSkipped);
	}

	public function testSkippedNotSent(): void
	{
		$vo = null;
		$exception = null;

		try {
			$vo = $this->processor->process([
				'required' => 'required',
			], SkippedFieldsVO::class);
		} catch (InvalidData $exception) {
			// Checked bellow
		}

		self::assertNull($vo);
		self::assertInstanceOf(InvalidData::class, $exception);

		self::assertSame(
			'requiredSkipped: string',
			$this->printer->printError($exception),
		);
	}

	public function testSkippedInvalidField(): void
	{
		$vo = $this->processor->process([
			'required' => 'required',
			'requiredSkipped' => null,
		], SkippedFieldsVO::class);
		$exception = null;

		try {
			$this->processor->processSkippedFields([
				'requiredSkipped',
			], $vo);
		} catch (InvalidData $exception) {
			// Checked bellow
		}

		self::assertInstanceOf(InvalidData::class, $exception);
		self::assertSame(
			'requiredSkipped: string',
			$this->printer->printError($exception),
		);
	}

	public function testSkippedObjectAlreadyFullyInitialized(): void
	{
		$vo = $this->processor->process([
			'required' => 'required',
			'requiredSkipped' => 'requiredSkipped',
		], SkippedFieldsVO::class);

		$this->processor->processSkippedFields([
			'requiredSkipped',
			'optionalSkipped',
		], $vo);

		$this->expectException(InvalidState::class);
		$this->expectExceptionMessage(
			'Cannot initialize fields "whatever" of "Tests\Orisai\ObjectMapper\Doubles\Skipped\SkippedFieldsVO"'
			. ' instance because it has no skipped fields.',
		);

		$this->processor->processSkippedFields(['whatever'], $vo);
	}

	public function testSkippedFieldAlreadyInitialized(): void
	{
		$vo = $this->processor->process([
			'required' => 'required',
			'requiredSkipped' => 'requiredSkipped',
		], SkippedFieldsVO::class);

		$this->expectException(InvalidState::class);
		$this->expectExceptionMessage(
			'Cannot initialize field "whatever" of "Tests\Orisai\ObjectMapper\Doubles\Skipped\SkippedFieldsVO"'
			. ' instance because it is already initialized or does not exist.',
		);

		$this->processor->processSkippedFields(['whatever'], $vo);
	}

	public function testAttributes(): void
	{
		if (PHP_VERSION_ID < 8_00_00) {
			self::markTestSkipped('Attributes are supported on PHP 8.0+');
		}

		$data = [
			'string' => 'foo',
		];

		$vo = $this->processor->process($data, AttributesVO::class);

		self::assertEquals(
			$vo,
			new AttributesVO('foo'),
		);
	}

	public function testObjectDefault(): void
	{
		if (PHP_VERSION_ID < 8_01_00) {
			self::markTestSkipped('Object default values are supported on PHP 8.1+');
		}

		$data = [];

		$vo = $this->processor->process($data, ObjectDefaultVO::class);
		self::assertEquals(new stdClass(), $vo->class);

		$vo2 = $this->processor->process($data, ObjectDefaultVO::class);
		self::assertEquals(new stdClass(), $vo2->class);

		self::assertNotSame($vo->class, $vo2->class);
	}

	public function testReadonlyClass(): void
	{
		if (PHP_VERSION_ID < 8_02_00) {
			self::markTestSkipped('Read-only classes are supported on PHP 8.2+');
		}

		$data = [
			'readonly' => 'value',
			'default2' => 'overridden',
		];

		$vo = $this->processor->process($data, ReadonlyClassVO::class);

		self::assertEquals(
			$vo,
			new ReadonlyClassVO('value', 'default', 'overridden'),
		);
	}

	public function testReadonlyProperties(): void
	{
		if (PHP_VERSION_ID < 8_01_00) {
			self::markTestSkipped('Read-only properties are supported on PHP 8.1+');
		}

		$data = [
			'readonly' => 'value',
			'default2' => 'overridden',
		];

		$vo = $this->processor->process($data, ReadonlyPropertiesVO::class);

		self::assertEquals(
			$vo,
			new ReadonlyPropertiesVO('value', 'default', 'overridden'),
		);
	}

	public function testDefaultsOverride(): void
	{
		if (PHP_VERSION_ID < 8_00_00) {
			self::markTestSkipped('Defaults override test uses features from PHP 8.0');
		}

		$vo = $this->processor->process([], DefaultsOverrideVO::class);

		self::assertSame('property', $vo->propertyDefault);
		self::assertSame('annotation', $vo->annotationDefault);
		self::assertSame('ctor', $vo->ctorDefault);
		self::assertSame('annotationCtor', $vo->annotationCtorDefault);
	}

	public function testConstructorPromotion(): void
	{
		if (PHP_VERSION_ID < 8_00_00) {
			self::markTestSkipped('Ctor promotion requires PHP 8.0');
		}

		$data = [
			'requiredString' => 'given',
			'requiredObject' => [],
			'requiredUntyped' => null,
		];

		$vo = $this->processor->process($data, ConstructorPromotedVO::class);

		self::assertEquals(
			$vo,
			new ConstructorPromotedVO(
				'given',
				new DefaultsVO(),
				null,
			),
		);

		self::assertSame('default', $vo->optionalString);
		self::assertNull($vo->optionalUntyped);
	}

	public function testNewInInitializers(): void
	{
		if (PHP_VERSION_ID < 8_01_00) {
			self::markTestSkipped('New in initializer requires PHP 8.1');
		}

		$data = [
			'requiredString' => 'given',
			'requiredObject' => [],
			'requiredUntyped' => null,
		];

		$vo = $this->processor->process($data, NewInInitializersVO::class);

		self::assertEquals(
			$vo,
			new NewInInitializersVO(
				'given',
				new DefaultsVO(),
				null,
				'default',
				new DefaultsVO(),
				null,
			),
		);
	}

	public function testChildOfInternalClass(): void
	{
		$data = [
			'field' => 'foo',
		];

		$vo = $this->processor->process($data, InternalClassExtendingVO::class);

		self::assertEquals(
			$vo,
			new InternalClassExtendingVO('foo'),
		);
	}

	private function isInitialized(MappedObject $object, string $property): bool
	{
		return (new ReflectionProperty($object, $property))->isInitialized($object);
	}

}
