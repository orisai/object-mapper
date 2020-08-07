<?php declare(strict_types = 1);

namespace Tests\Orisai\ObjectMapper\Unit\Rules;

use Generator;
use Orisai\ObjectMapper\Exception\ValueDoesNotMatch;
use Orisai\ObjectMapper\Rules\FloatArgs;
use Orisai\ObjectMapper\Rules\FloatRule;
use Orisai\ObjectMapper\Types\SimpleValueType;
use Tests\Orisai\ObjectMapper\Toolkit\RuleTestCase;
use function assert;

final class FloatRuleTest extends RuleTestCase
{

	private FloatRule $rule;

	protected function setUp(): void
	{
		parent::setUp();
		$this->rule = new FloatRule();
	}

	/**
	 * @dataProvider provideValidValues
	 * @param mixed $value
	 * @param array<mixed> $args
	 */
	public function testProcessValid($value, float $expected, array $args = []): void
	{
		$processed = $this->rule->processValue(
			$value,
			FloatArgs::fromArray($this->rule->resolveArgs($args, $this->ruleArgsContext())),
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
			[
				FloatRule::UNSIGNED => false,
				FloatRule::MIN => 100,
				FloatRule::MAX => 100,
			],
		];

		yield [
			100.12,
			100.12,
			[
				FloatRule::UNSIGNED => false,
				FloatRule::MIN => 100.12,
				FloatRule::MAX => 100.12,
			],
		];

		yield [
			-100,
			-100.0,
			[
				FloatRule::UNSIGNED => false,
			],
		];

		yield [
			-100.12,
			-100.12,
			[
				FloatRule::UNSIGNED => false,
			],
		];
	}

	/**
	 * @dataProvider provideFloatLikeValues
	 * @param mixed $value
	 */
	public function testProcessFloatLike($value, float $expected): void
	{
		$processed = $this->rule->processValue(
			$value,
			FloatArgs::fromArray($this->rule->resolveArgs([
				FloatRule::CAST_FLOAT_LIKE => true,
				FloatRule::UNSIGNED => false,
			], $this->ruleArgsContext())),
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
	 * @dataProvider provideInvalidValues
	 * @param mixed $value
	 */
	public function testProcessInvalid($value): void
	{
		$exception = null;

		try {
			$this->rule->processValue(
				$value,
				FloatArgs::fromArray($this->rule->resolveArgs([], $this->ruleArgsContext())),
				$this->fieldContext(),
			);
		} catch (ValueDoesNotMatch $exception) {
			$type = $exception->getInvalidType();
			assert($type instanceof SimpleValueType);

			self::assertSame('float', $type->getType());
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

		try {
			$this->rule->processValue(
				'100',
				FloatArgs::fromArray($this->rule->resolveArgs([
					FloatRule::CAST_FLOAT_LIKE => true,
					FloatRule::MAX => 10,
				], $this->ruleArgsContext())),
				$this->fieldContext(),
			);
		} catch (ValueDoesNotMatch $exception) {
			$type = $exception->getInvalidType();
			assert($type instanceof SimpleValueType);

			self::assertSame('float', $type->getType());
			self::assertTrue($type->hasInvalidParameters());
			self::assertTrue($type->isParameterInvalid(FloatRule::MAX));
		}

		self::assertNotNull($exception);
	}

	public function testProcessInvalidParametersUnsignedAndMin(): void
	{
		$exception = null;

		try {
			$this->rule->processValue(
				'-100',
				FloatArgs::fromArray($this->rule->resolveArgs([
					FloatRule::CAST_FLOAT_LIKE => true,
					FloatRule::MIN => 10,
					FloatRule::UNSIGNED => true,
				], $this->ruleArgsContext())),
				$this->fieldContext(),
			);
		} catch (ValueDoesNotMatch $exception) {
			$type = $exception->getInvalidType();
			assert($type instanceof SimpleValueType);

			self::assertSame('float', $type->getType());
			self::assertTrue($type->hasInvalidParameters());
			self::assertTrue($type->isParameterInvalid(FloatRule::MIN));
			self::assertTrue($type->isParameterInvalid(FloatRule::UNSIGNED));
		}

		self::assertNotNull($exception);
	}

	public function testType(): void
	{
		$args = FloatArgs::fromArray($this->rule->resolveArgs([], $this->ruleArgsContext()));

		$type = $this->rule->createType($args, $this->typeContext);

		self::assertEquals(
			$this->rule->createType($args, $this->fieldContext()),
			$type,
		);

		self::assertSame('float', $type->getType());
		self::assertSame(
			[
				'unsigned' => true,
				'min' => null,
				'max' => null,
				'acceptsFloatLike' => false,
			],
			$type->getParameters(),
		);
	}

}
