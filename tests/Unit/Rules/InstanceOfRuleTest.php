<?php declare(strict_types = 1);

namespace Tests\Orisai\ObjectMapper\Unit\Rules;

use Generator;
use Orisai\ObjectMapper\Exception\ValueDoesNotMatch;
use Orisai\ObjectMapper\Rules\InstanceOfArgs;
use Orisai\ObjectMapper\Rules\InstanceOfRule;
use Orisai\ObjectMapper\Rules\Rule;
use Orisai\ObjectMapper\Types\SimpleValueType;
use stdClass;
use Tests\Orisai\ObjectMapper\Toolkit\ProcessingTestCase;

final class InstanceOfRuleTest extends ProcessingTestCase
{

	private InstanceOfRule $rule;

	protected function setUp(): void
	{
		parent::setUp();
		$this->rule = new InstanceOfRule();
	}

	/**
	 * @param array<mixed> $args
	 *
	 * @dataProvider provideResolveValid
	 */
	public function testResolveValid(array $args, InstanceOfArgs $expectedArgs): void
	{
		$resolvedArgs = $this->rule->resolveArgs($args, $this->argsFieldContext());
		self::assertEquals($expectedArgs, $resolvedArgs);
	}

	public static function provideResolveValid(): Generator
	{
		yield [
			[
				InstanceOfRule::Type => stdClass::class,
			],
			new InstanceOfArgs(stdClass::class),
		];

		yield [
			[
				InstanceOfRule::Type => Rule::class,
			],
			new InstanceOfArgs(Rule::class),
		];

		yield [
			[
				InstanceOfRule::Type => InstanceOfRule::class,
			],
			new InstanceOfArgs(InstanceOfRule::class),
		];
	}

	/**
	 * @param mixed $value
	 *
	 * @dataProvider provideValidValues
	 */
	public function testProcessValid($value): void
	{
		$processed = $this->rule->processValue(
			$value,
			new InstanceOfArgs(stdClass::class),
			$this->fieldContext(),
		);

		self::assertSame($value, $processed);
	}

	/**
	 * @return Generator<array<mixed>>
	 */
	public function provideValidValues(): Generator
	{
		yield [new stdClass()];
		yield [new class extends stdClass {

		}];
	}

	/**
	 * @param mixed $value
	 *
	 * @dataProvider provideInvalidValues
	 */
	public function testProcessInvalid($value): void
	{
		$exception = null;

		try {
			$this->rule->processValue(
				$value,
				new InstanceOfArgs(stdClass::class),
				$this->fieldContext(),
			);
		} catch (ValueDoesNotMatch $exception) {
			$type = $exception->getType();
			self::assertInstanceOf(SimpleValueType::class, $type);

			self::assertSame(stdClass::class, $type->getName());
			self::assertSame($value, $exception->getValue()->get());
		}

		self::assertNotNull($exception);
	}

	/**
	 * @return Generator<array<mixed>>
	 */
	public function provideInvalidValues(): Generator
	{
		yield [[]];
		yield [['foo', 123, 123.456, true, false]];
		yield [''];
		yield [0];
		yield [true];
		yield [false];
		yield ['foo'];
		yield [123];
		yield [123.456];
		yield [new class {

		}];
	}

	public function testType(): void
	{
		$args = new InstanceOfArgs(stdClass::class);

		$type = $this->rule->createType($args, $this->createTypeContext());

		self::assertEquals(
			$this->rule->createType($args, $this->fieldContext()),
			$type,
		);

		self::assertSame(stdClass::class, $type->getName());
		self::assertSame(
			[],
			$type->getParameters(),
		);
	}

}
