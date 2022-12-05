<?php declare(strict_types = 1);

namespace Tests\Orisai\ObjectMapper\Unit\Rules;

use Generator;
use Orisai\ObjectMapper\Exception\ValueDoesNotMatch;
use Orisai\ObjectMapper\Rules\FloatArgs;
use Orisai\ObjectMapper\Rules\FloatRule;
use Orisai\ObjectMapper\Types\SimpleValueType;
use Tests\Orisai\ObjectMapper\Toolkit\ProcessingTestCase;

final class FloatRuleTest extends ProcessingTestCase
{

	private FloatRule $rule;

	protected function setUp(): void
	{
		parent::setUp();
		$this->rule = new FloatRule();
	}

	/**
	 * @param mixed $value
	 *
	 * @dataProvider provideValidValues
	 */
	public function testProcessValid($value, float $expected, ?FloatArgs $args = null): void
	{
		$processed = $this->rule->processValue(
			$value,
			$args ?? new FloatArgs(),
			$this->fieldContext(),
		);

		self::assertSame($expected, $processed);
	}

	/**
	 * @return Generator<array<mixed>>
	 */
	public function provideValidValues(): Generator
	{
		yield [100, 100.0];
		yield [100.12, 100.12];
		yield [0, 0.0];
		yield [0.12, 0.12];
		yield [
			100,
			100.0,
			new FloatArgs(100, 100, false),
		];

		yield [
			100.12,
			100.12,
			new FloatArgs(100.12, 100.12, false),
		];

		yield [
			-100,
			-100.0,
			new FloatArgs(null, null, false),
		];

		yield [
			-100.12,
			-100.12,
			new FloatArgs(null, null, false),
		];
	}

	/**
	 * @param mixed $value
	 *
	 * @dataProvider provideFloatLikeValues
	 */
	public function testProcessFloatLike($value, float $expected): void
	{
		$processed = $this->rule->processValue(
			$value,
			new FloatArgs(null, null, false, true),
			$this->fieldContext(),
		);

		self::assertSame($expected, $processed);
	}

	/**
	 * @return Generator<array<mixed>>
	 */
	public function provideFloatLikeValues(): Generator
	{
		yield ['0', 0.0];
		yield ['0.12', 0.12];
		yield [10, 10.0];
		yield [10.12, 10.12];
		yield ['+10', 10.0];
		yield ['+10.12', 10.12];
		yield ['100', 100.0];
		yield ['100.12', 100.12];
		yield ['100,12', 100.12];
		yield ['100 000', 100_000.0];
		yield ['100 000.12', 100_000.12];
		yield ['-100', -100.0];
		yield ['-100 000', -100_000.0];
		yield ['-100 000.12', -100_000.12];
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
				new FloatArgs(),
				$this->fieldContext(),
			);
		} catch (ValueDoesNotMatch $exception) {
			$type = $exception->getType();
			self::assertInstanceOf(SimpleValueType::class, $type);

			self::assertSame('float', $type->getName());
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
		yield ['0 foo'];
		yield ['100'];
		yield ['100.12'];
		yield [null];
	}

	public function testProcessInvalidParameterMax(): void
	{
		$exception = null;
		$value = '100';

		try {
			$this->rule->processValue(
				$value,
				new FloatArgs(null, 10, true, true),
				$this->fieldContext(),
			);
		} catch (ValueDoesNotMatch $exception) {
			$type = $exception->getType();
			self::assertInstanceOf(SimpleValueType::class, $type);

			self::assertSame('float', $type->getName());
			self::assertTrue($type->hasInvalidParameters());
			self::assertTrue($type->getParameter(FloatRule::Max)->isInvalid());
			self::assertSame($value, $exception->getValue()->get());
		}

		self::assertNotNull($exception);
	}

	public function testProcessInvalidParametersUnsignedAndMin(): void
	{
		$exception = null;
		$value = '-100';

		try {
			$this->rule->processValue(
				$value,
				new FloatArgs(10, null, true, true),
				$this->fieldContext(),
			);
		} catch (ValueDoesNotMatch $exception) {
			$type = $exception->getType();
			self::assertInstanceOf(SimpleValueType::class, $type);

			self::assertSame('float', $type->getName());
			self::assertTrue($type->hasInvalidParameters());
			self::assertTrue($type->getParameter(FloatRule::Min)->isInvalid());
			self::assertTrue($type->getParameter(FloatRule::Unsigned)->isInvalid());
			self::assertSame($value, $exception->getValue()->get());
		}

		self::assertNotNull($exception);
	}

	public function testType(): void
	{
		$args = $this->rule->resolveArgs([], $this->ruleArgsContext());

		$type = $this->rule->createType($args, $this->createTypeContext());

		self::assertEquals(
			$this->rule->createType($args, $this->fieldContext()),
			$type,
		);

		self::assertSame('float', $type->getName());
		self::assertCount(0, $type->getParameters());
	}

	public function testTypeWithArgs(): void
	{
		$args = new FloatArgs(10, 100, true, true);

		$type = $this->rule->createType($args, $this->createTypeContext());

		self::assertEquals(
			$this->rule->createType($args, $this->fieldContext()),
			$type,
		);

		self::assertSame('float', $type->getName());

		self::assertCount(4, $type->getParameters());
		self::assertTrue($type->hasParameter(FloatRule::Min));
		self::assertSame(10.0, $type->getParameter(FloatRule::Min)->getValue());
		self::assertTrue($type->hasParameter(FloatRule::Max));
		self::assertSame(100.0, $type->getParameter(FloatRule::Max)->getValue());
		self::assertTrue($type->hasParameter('unsigned'));
		self::assertFalse($type->getParameter('unsigned')->hasValue());
		self::assertTrue($type->hasParameter('acceptsNumericString'));
		self::assertFalse($type->getParameter('acceptsNumericString')->hasValue());
	}

}
