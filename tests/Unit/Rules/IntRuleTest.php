<?php declare(strict_types = 1);

namespace Tests\Orisai\ObjectMapper\Unit\Rules;

use Generator;
use Orisai\ObjectMapper\Exception\ValueDoesNotMatch;
use Orisai\ObjectMapper\Rules\IntArgs;
use Orisai\ObjectMapper\Rules\IntRule;
use Orisai\ObjectMapper\Types\SimpleValueType;
use Tests\Orisai\ObjectMapper\Toolkit\RuleTestCase;

final class IntRuleTest extends RuleTestCase
{

	private IntRule $rule;

	protected function setUp(): void
	{
		parent::setUp();
		$this->rule = new IntRule();
	}

	/**
	 * @param mixed $value
	 *
	 * @dataProvider provideValidValues
	 */
	public function testProcessValid($value, ?IntArgs $args = null): void
	{
		$processed = $this->rule->processValue(
			$value,
			$args ?? new IntArgs(),
			$this->fieldContext(),
		);

		self::assertSame($value, $processed);
	}

	/**
	 * @return Generator<array<mixed>>
	 */
	public function provideValidValues(): Generator
	{
		yield [100];
		yield [0];
		yield [
			100,
			new IntArgs(100, 100, false),
		];

		yield [
			-100,
			new IntArgs(null, null, false),
		];
	}

	/**
	 * @param mixed $value
	 *
	 * @dataProvider provideIntLikeValues
	 */
	public function testProcessIntLike($value, int $expected): void
	{
		$processed = $this->rule->processValue(
			$value,
			new IntArgs(null, null, false, true),
			$this->fieldContext(),
		);

		self::assertSame($expected, $processed);
	}

	/**
	 * @return Generator<array<mixed>>
	 */
	public function provideIntLikeValues(): Generator
	{
		yield ['0', 0];
		yield [10, 10];
		yield ['+10', 10];
		yield ['100', 100];
		yield ['100 000', 100_000];
		yield ['-100', -100];
		yield ['-100 000', -100_000];
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
				new IntArgs(),
				$this->fieldContext(),
			);
		} catch (ValueDoesNotMatch $exception) {
			$type = $exception->getType();
			self::assertInstanceOf(SimpleValueType::class, $type);

			self::assertSame('int', $type->getName());
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
		yield [0.012];
		yield [123.456];
		yield ['0 foo'];
		yield ['100'];
		yield [null];
	}

	public function testProcessInvalidParameterMax(): void
	{
		$exception = null;
		$value = '100';

		try {
			$this->rule->processValue(
				$value,
				new IntArgs(null, 10, true, true),
				$this->fieldContext(),
			);
		} catch (ValueDoesNotMatch $exception) {
			$type = $exception->getType();
			self::assertInstanceOf(SimpleValueType::class, $type);

			self::assertSame('int', $type->getName());
			self::assertTrue($type->hasInvalidParameters());
			self::assertTrue($type->getParameter(IntRule::Max)->isInvalid());
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
				new IntArgs(10, null, true, true),
				$this->fieldContext(),
			);
		} catch (ValueDoesNotMatch $exception) {
			$type = $exception->getType();
			self::assertInstanceOf(SimpleValueType::class, $type);

			self::assertSame('int', $type->getName());
			self::assertTrue($type->hasInvalidParameters());
			self::assertTrue($type->getParameter(IntRule::Min)->isInvalid());
			self::assertTrue($type->getParameter(IntRule::Unsigned)->isInvalid());
			self::assertSame($value, $exception->getValue()->get());
		}

		self::assertNotNull($exception);
	}

	public function testType(): void
	{
		$args = new IntArgs();

		$type = $this->rule->createType($args, $this->typeContext);

		self::assertEquals(
			$this->rule->createType($args, $this->fieldContext()),
			$type,
		);

		self::assertSame('int', $type->getName());
		self::assertCount(0, $type->getParameters());
	}

	public function testTypeWithArgs(): void
	{
		$args = new IntArgs(10, 100, true, true);

		$type = $this->rule->createType($args, $this->typeContext);

		self::assertEquals(
			$this->rule->createType($args, $this->fieldContext()),
			$type,
		);

		self::assertSame('int', $type->getName());

		self::assertCount(4, $type->getParameters());
		self::assertTrue($type->hasParameter(IntRule::Min));
		self::assertSame(10, $type->getParameter(IntRule::Min)->getValue());
		self::assertTrue($type->hasParameter(IntRule::Max));
		self::assertSame(100, $type->getParameter(IntRule::Max)->getValue());
		self::assertTrue($type->hasParameter('unsigned'));
		self::assertFalse($type->getParameter('unsigned')->hasValue());
		self::assertTrue($type->hasParameter('acceptsNumericString'));
		self::assertFalse($type->getParameter('acceptsNumericString')->hasValue());
	}

}
