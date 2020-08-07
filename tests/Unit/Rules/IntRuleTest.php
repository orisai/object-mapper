<?php declare(strict_types = 1);

namespace Tests\Orisai\ObjectMapper\Unit\Rules;

use Generator;
use Orisai\ObjectMapper\Exception\ValueDoesNotMatch;
use Orisai\ObjectMapper\Rules\IntArgs;
use Orisai\ObjectMapper\Rules\IntRule;
use Orisai\ObjectMapper\Types\SimpleValueType;
use Tests\Orisai\ObjectMapper\Toolkit\RuleTestCase;
use function assert;

final class IntRuleTest extends RuleTestCase
{

	private IntRule $rule;

	protected function setUp(): void
	{
		parent::setUp();
		$this->rule = new IntRule();
	}

	/**
	 * @dataProvider provideValidValues
	 * @param mixed $value
	 * @param array<mixed> $args
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
	 * @dataProvider provideIntLikeValues
	 * @param mixed $value
	 */
	public function testProcessIntLike($value, int $expected): void
	{
		$processed = $this->rule->processValue(
			$value,
			IntArgs::fromArray($this->rule->resolveArgs([
				IntRule::CAST_INT_LIKE => true,
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
	 * @dataProvider provideInvalidValues
	 * @param mixed $value
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
			assert($type instanceof SimpleValueType);

			self::assertSame('int', $type->getType());
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

		try {
			$this->rule->processValue(
				'100',
				IntArgs::fromArray($this->rule->resolveArgs([
					IntRule::CAST_INT_LIKE => true,
					IntRule::MAX => 10,
				], $this->ruleArgsContext())),
				$this->fieldContext(),
			);
		} catch (ValueDoesNotMatch $exception) {
			$type = $exception->getInvalidType();
			assert($type instanceof SimpleValueType);

			self::assertSame('int', $type->getType());
			self::assertTrue($type->hasInvalidParameters());
			self::assertTrue($type->isParameterInvalid(IntRule::MAX));
		}

		self::assertNotNull($exception);
	}

	public function testProcessInvalidParametersUnsignedAndMin(): void
	{
		$exception = null;

		try {
			$this->rule->processValue(
				'-100',
				IntArgs::fromArray($this->rule->resolveArgs([
					IntRule::CAST_INT_LIKE => true,
					IntRule::MIN => 10,
					IntRule::UNSIGNED => true,
				], $this->ruleArgsContext())),
				$this->fieldContext(),
			);
		} catch (ValueDoesNotMatch $exception) {
			$type = $exception->getInvalidType();
			assert($type instanceof SimpleValueType);

			self::assertSame('int', $type->getType());
			self::assertTrue($type->hasInvalidParameters());
			self::assertTrue($type->isParameterInvalid(IntRule::MIN));
			self::assertTrue($type->isParameterInvalid(IntRule::UNSIGNED));
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

		self::assertSame('int', $type->getType());
		self::assertSame(
			[
				'unsigned' => true,
				'min' => null,
				'max' => null,
				'acceptsIntLike' => false,
			],
			$type->getParameters(),
		);
	}

}
