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
	 * @param array<mixed> $args
	 *
	 * @dataProvider provideValidValues
	 */
	public function testProcessValid($value, array $args = []): void
	{
		$processed = $this->rule->processValue(
			$value,
			IntArgs::fromArray($this->rule->resolveArgs($args, $this->ruleArgsContext())),
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
			[
				IntRule::UNSIGNED => false,
				IntRule::MIN => 100,
				IntRule::MAX => 100,
			],
		];

		yield [
			-100,
			[
				IntRule::UNSIGNED => false,
			],
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
			IntArgs::fromArray($this->rule->resolveArgs([
				IntRule::CAST_NUMERIC_STRING => true,
				IntRule::UNSIGNED => false,
			], $this->ruleArgsContext())),
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
				IntArgs::fromArray($this->rule->resolveArgs([], $this->ruleArgsContext())),
				$this->fieldContext(),
			);
		} catch (ValueDoesNotMatch $exception) {
			$type = $exception->getInvalidType();
			self::assertInstanceOf(SimpleValueType::class, $type);

			self::assertSame('int', $type->getName());
			self::assertSame($value, $exception->getInvalidValue());
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
				IntArgs::fromArray($this->rule->resolveArgs([
					IntRule::CAST_NUMERIC_STRING => true,
					IntRule::MAX => 10,
				], $this->ruleArgsContext())),
				$this->fieldContext(),
			);
		} catch (ValueDoesNotMatch $exception) {
			$type = $exception->getInvalidType();
			self::assertInstanceOf(SimpleValueType::class, $type);

			self::assertSame('int', $type->getName());
			self::assertTrue($type->hasInvalidParameters());
			self::assertTrue($type->getParameter(IntRule::MAX)->isInvalid());
			self::assertSame($value, $exception->getInvalidValue());
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
				IntArgs::fromArray($this->rule->resolveArgs([
					IntRule::CAST_NUMERIC_STRING => true,
					IntRule::MIN => 10,
					IntRule::UNSIGNED => true,
				], $this->ruleArgsContext())),
				$this->fieldContext(),
			);
		} catch (ValueDoesNotMatch $exception) {
			$type = $exception->getInvalidType();
			self::assertInstanceOf(SimpleValueType::class, $type);

			self::assertSame('int', $type->getName());
			self::assertTrue($type->hasInvalidParameters());
			self::assertTrue($type->getParameter(IntRule::MIN)->isInvalid());
			self::assertTrue($type->getParameter(IntRule::UNSIGNED)->isInvalid());
			self::assertSame($value, $exception->getInvalidValue());
		}

		self::assertNotNull($exception);
	}

	public function testType(): void
	{
		$args = IntArgs::fromArray($this->rule->resolveArgs([], $this->ruleArgsContext()));

		$type = $this->rule->createType($args, $this->typeContext);

		self::assertEquals(
			$this->rule->createType($args, $this->fieldContext()),
			$type,
		);

		self::assertSame('int', $type->getName());
		self::assertCount(1, $type->getParameters());
		self::assertTrue($type->hasParameter('unsigned'));
	}

	public function testTypeWithArgs(): void
	{
		$args = IntArgs::fromArray($this->rule->resolveArgs([
			IntRule::UNSIGNED => false,
			IntRule::MIN => 10,
			IntRule::MAX => 100,
			IntRule::CAST_NUMERIC_STRING => true,
		], $this->ruleArgsContext()));

		$type = $this->rule->createType($args, $this->typeContext);

		self::assertEquals(
			$this->rule->createType($args, $this->fieldContext()),
			$type,
		);

		self::assertSame('int', $type->getName());

		self::assertCount(3, $type->getParameters());
		self::assertTrue($type->hasParameter(IntRule::MIN));
		self::assertSame(10, $type->getParameter(IntRule::MIN)->getValue());
		self::assertTrue($type->hasParameter(IntRule::MAX));
		self::assertSame(100, $type->getParameter(IntRule::MAX)->getValue());
		self::assertTrue($type->hasParameter('acceptsNumericString'));
		self::assertFalse($type->getParameter('acceptsNumericString')->hasValue());
	}

}
