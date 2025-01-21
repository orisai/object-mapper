<?php declare(strict_types = 1);

namespace Tests\Orisai\ObjectMapper\Unit\Rules;

use Generator;
use Orisai\ObjectMapper\Args\EmptyArgs;
use Orisai\ObjectMapper\Exception\ValueDoesNotMatch;
use Orisai\ObjectMapper\Meta\Compile\RuleCompileMeta;
use Orisai\ObjectMapper\Meta\Runtime\RuleRuntimeMeta;
use Orisai\ObjectMapper\Meta\Shared\DefaultValueMeta;
use Orisai\ObjectMapper\Rules\ArrayShapeArgs;
use Orisai\ObjectMapper\Rules\ArrayShapeRule;
use Orisai\ObjectMapper\Rules\IntRule;
use Orisai\ObjectMapper\Rules\MappedObjectRule;
use Orisai\ObjectMapper\Rules\MixedRule;
use Orisai\ObjectMapper\Rules\NullArgs;
use Orisai\ObjectMapper\Rules\NullRule;
use Orisai\ObjectMapper\Rules\ScalarRule;
use Orisai\ObjectMapper\Rules\StringArgs;
use Orisai\ObjectMapper\Rules\StringRule;
use Orisai\ObjectMapper\Types\ArrayShapeType;
use Orisai\ObjectMapper\Types\SimpleValueType;
use Tests\Orisai\ObjectMapper\Doubles\EmptyVO;
use Tests\Orisai\ObjectMapper\Doubles\Rules\AlwaysInvalidRule;
use Tests\Orisai\ObjectMapper\Toolkit\ProcessingTestCase;

final class ArrayShapeRuleTest extends ProcessingTestCase
{

	private ArrayShapeRule $rule;

	protected function setUp(): void
	{
		parent::setUp();
		$this->rule = new ArrayShapeRule();
		$this->ruleManager->addRule(new AlwaysInvalidRule());
	}

	/**
	 * @param array<mixed> $args
	 *
	 * @dataProvider provideResolveValid
	 */
	public function testResolveValid(array $args, ArrayShapeArgs $expectedArgs): void
	{
		$resolvedArgs = $this->rule->resolveArgs($args, $this->argsFieldContext());
		self::assertEquals($expectedArgs, $resolvedArgs);
	}

	public static function provideResolveValid(): Generator
	{
		yield [
			[
				ArrayShapeRule::Fields => [
					'foo' => new RuleCompileMeta(MixedRule::class),
				],
			],
			new ArrayShapeArgs([
				'foo' => new RuleRuntimeMeta(MixedRule::class, new EmptyArgs()),
			]),
		];

		yield [
			[
				ArrayShapeRule::Fields => [
					1 => new RuleCompileMeta(ScalarRule::class),
					'foo' => new RuleCompileMeta(StringRule::class),
				],
			],
			new ArrayShapeArgs([
				1 => new RuleRuntimeMeta(ScalarRule::class, new EmptyArgs()),
				'foo' => new RuleRuntimeMeta(StringRule::class, new StringArgs(null, false, null, null)),
			]),
		];
	}

	public function testProcessValid(): void
	{
		$value = [
			123 => 456,
			'string' => 'test',
			'object' => [],
		];
		$defaults = ['lorem', 'ipsum'];

		$processed = $this->rule->processValue(
			$value,
			new ArrayShapeArgs(
				[
					'object' => $this->ruleRuntimeMeta(
						MappedObjectRule::class,
						[
							'class' => EmptyVO::class,
						],
					),
					'string' => $this->ruleRuntimeMeta(StringRule::class),
					123 => $this->ruleRuntimeMeta(IntRule::class),
				],
			),
			$this->dependencies->servicesContext,
			$this->dependencies->createPropertyContext(DefaultValueMeta::fromValue($defaults)),
			$this->dependencies->createDynamicContext(null, true),
		);

		self::assertEquals(
			[
				123 => 456,
				'string' => 'test',
				'object' => new EmptyVO(),
			],
			$processed,
		);
	}

	public function testProcessInvalidType(): void
	{
		$exception = null;
		$value = null;

		try {
			$this->rule->processValue(
				$value,
				new ArrayShapeArgs(
					[
						'foo' => new RuleRuntimeMeta(MixedRule::class, new EmptyArgs()),
					],
				),
				$this->dependencies->servicesContext,
				$this->dependencies->createPropertyContext(),
				$this->dependencies->createDynamicContext(),
			);
		} catch (ValueDoesNotMatch $exception) {
			$type = $exception->getType();
			self::assertInstanceOf(ArrayShapeType::class, $type);

			self::assertTrue($type->isInvalid());
			self::assertSame($value, $exception->getValue()->get());
		}

		self::assertNotNull($exception);
	}

	public function testProcessInvalidFields(): void
	{
		$exception = null;
		$value = [
			123 => 'foo',
			'string' => 456,
			'null' => null,
		];

		try {
			$this->rule->processValue(
				$value,
				new ArrayShapeArgs(
					[
						'null' => $this->ruleRuntimeMeta(NullRule::class),
						'string' => $this->ruleRuntimeMeta(StringRule::class),
						123 => $this->ruleRuntimeMeta(IntRule::class),
					],
				),
				$this->dependencies->servicesContext,
				$this->dependencies->createPropertyContext(),
				$this->dependencies->createDynamicContext(),
			);
		} catch (ValueDoesNotMatch $exception) {
			$type = $exception->getType();
			self::assertInstanceOf(ArrayShapeType::class, $type);

			self::assertFalse($type->isInvalid());
			self::assertFalse($exception->getValue()->has());

			self::assertCount(2, $type->getInvalidFields());
			self::assertCount(0, $type->getErrors());
		}

		self::assertNotNull($exception);
	}

	public function testProcessUnknownFields(): void
	{
		$exception = null;
		$value = [
			'string' => 'test',
			123 => 456,
			'null' => null,
			'unknown1' => null,
			'unknown2' => 'test',
		];

		try {
			$this->rule->processValue(
				$value,
				new ArrayShapeArgs(
					[
						'null' => $this->ruleRuntimeMeta(NullRule::class),
						'string' => $this->ruleRuntimeMeta(StringRule::class),
						123 => $this->ruleRuntimeMeta(IntRule::class),
					],
				),
				$this->dependencies->servicesContext,
				$this->dependencies->createPropertyContext(),
				$this->dependencies->createDynamicContext(),
			);
		} catch (ValueDoesNotMatch $exception) {
			$type = $exception->getType();
			self::assertInstanceOf(ArrayShapeType::class, $type);

			self::assertFalse($type->isInvalid());
			self::assertFalse($exception->getValue()->has());

			self::assertCount(2, $type->getInvalidFields());
			self::assertCount(0, $type->getErrors());
			self::assertSame(
				'Field is unknown.',
				$type->getInvalidFields()['unknown1']->getType()->getMessage(),
			);
			self::assertSame(
				'Field is unknown.',
				$type->getInvalidFields()['unknown2']->getType()->getMessage(),
			);
		}

		self::assertNotNull($exception);
	}

	public function testProcessMissingFields(): void
	{
		$exception = null;
		$value = [
			'string' => 'test',
		];

		try {
			$this->rule->processValue(
				$value,
				new ArrayShapeArgs(
					[
						'null' => $this->ruleRuntimeMeta(NullRule::class),
						'string' => $this->ruleRuntimeMeta(StringRule::class),
						123 => $this->ruleRuntimeMeta(IntRule::class),
					],
				),
				$this->dependencies->servicesContext,
				$this->dependencies->createPropertyContext(),
				$this->dependencies->createDynamicContext(),
			);
		} catch (ValueDoesNotMatch $exception) {
			$type = $exception->getType();
			self::assertInstanceOf(ArrayShapeType::class, $type);

			self::assertFalse($type->isInvalid());
			self::assertFalse($exception->getValue()->has());

			self::assertCount(2, $type->getInvalidFields());
			self::assertCount(0, $type->getErrors());
			self::assertSame(
				'null',
				$type->getInvalidFields()['null']->getType()->getName(),
			);
			self::assertSame(
				'int',
				$type->getInvalidFields()[123]->getType()->getName(),
			);
		}

		self::assertNotNull($exception);
	}

	public function testProcessTypoInFieldName(): void
	{
		$exception = null;
		$value = [
			'from' => null,
		];

		try {
			$this->rule->processValue(
				$value,
				new ArrayShapeArgs(
					[
						'form' => $this->ruleRuntimeMeta(NullRule::class),
					],
				),
				$this->dependencies->servicesContext,
				$this->dependencies->createPropertyContext(),
				$this->dependencies->createDynamicContext(),
			);
		} catch (ValueDoesNotMatch $exception) {
			$type = $exception->getType();
			self::assertInstanceOf(ArrayShapeType::class, $type);

			self::assertFalse($type->isInvalid());
			self::assertFalse($exception->getValue()->has());

			self::assertCount(2, $type->getInvalidFields());
			self::assertCount(0, $type->getErrors());
			self::assertSame(
				"Field is unknown, did you mean 'form'?",
				$type->getInvalidFields()['from']->getType()->getMessage(),
			);
			self::assertSame(
				'null',
				$type->getInvalidFields()['form']->getType()->getName(),
			);
		}

		self::assertNotNull($exception);
	}

	public function testProcessDuplicateWithTypo(): void
	{
		$exception = null;
		$value = [
			'form' => null,
			'from' => null,
		];

		try {
			$this->rule->processValue(
				$value,
				new ArrayShapeArgs(
					[
						'form' => $this->ruleRuntimeMeta(NullRule::class),
					],
				),
				$this->dependencies->servicesContext,
				$this->dependencies->createPropertyContext(),
				$this->dependencies->createDynamicContext(),
			);
		} catch (ValueDoesNotMatch $exception) {
			$type = $exception->getType();
			self::assertInstanceOf(ArrayShapeType::class, $type);

			self::assertFalse($type->isInvalid());
			self::assertFalse($exception->getValue()->has());

			self::assertCount(1, $type->getInvalidFields());
			self::assertCount(0, $type->getErrors());
			self::assertSame(
				'Field is unknown.',
				$type->getInvalidFields()['from']->getType()->getMessage(),
			);
		}

		self::assertNotNull($exception);
	}

	public function testType(): void
	{
		$args = new ArrayShapeArgs([
			'foo' => new RuleRuntimeMeta(MixedRule::class, new EmptyArgs()),
			'bar' => new RuleRuntimeMeta(NullRule::class, new NullArgs(false)),
			123 => new RuleRuntimeMeta(MixedRule::class, new EmptyArgs()),
		]);

		$type = $this->rule->createType(
			$args,
			$this->dependencies->servicesContext,
			$this->dependencies->createDynamicContext(),
		);

		self::assertEquals(
			$this->rule->createType(
				$args,
				$this->dependencies->servicesContext,
				$this->dependencies->createDynamicContext(),
			),
			$type,
		);

		self::assertCount(3, $type->getFields());
		self::assertEquals(
			[
				'foo' => new SimpleValueType('mixed'),
				'bar' => new SimpleValueType('null'),
				123 => new SimpleValueType('mixed'),
			],
			$type->getFields(),
		);
	}

	public function testTypeEmpty(): void
	{
		$args = new ArrayShapeArgs([]);

		$type = $this->rule->createType(
			$args,
			$this->dependencies->servicesContext,
			$this->dependencies->createDynamicContext(),
		);

		self::assertEquals(
			$this->rule->createType(
				$args,
				$this->dependencies->servicesContext,
				$this->dependencies->createDynamicContext(),
			),
			$type,
		);

		self::assertCount(0, $type->getFields());
	}

}
