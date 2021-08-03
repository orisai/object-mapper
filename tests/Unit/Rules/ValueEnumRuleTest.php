<?php declare(strict_types = 1);

namespace Tests\Orisai\ObjectMapper\Unit\Rules;

use Generator;
use Orisai\ObjectMapper\Exception\ValueDoesNotMatch;
use Orisai\ObjectMapper\Rules\ValueEnumArgs;
use Orisai\ObjectMapper\Rules\ValueEnumRule;
use Orisai\ObjectMapper\Types\EnumType;
use Tests\Orisai\ObjectMapper\Toolkit\RuleTestCase;
use function assert;

final class ValueEnumRuleTest extends RuleTestCase
{

	private ValueEnumRule $rule;

	protected function setUp(): void
	{
		parent::setUp();
		$this->rule = new ValueEnumRule();
	}

	/**
	 * @param mixed $value
	 * @param array<mixed> $args
	 *
	 * @dataProvider provideValidValues
	 */
	public function testProcessValid($value, array $args): void
	{
		$processed = $this->rule->processValue(
			$value,
			ValueEnumArgs::fromArray($this->rule->resolveArgs($args, $this->ruleArgsContext())),
			$this->fieldContext(),
		);

		self::assertSame($value, $processed);
	}

	/**
	 * @return Generator<array<mixed>>
	 */
	public function provideValidValues(): Generator
	{
		yield [
			'foo',
			[
				ValueEnumRule::VALUES => ['foo', 'bar'],
			],
		];

		yield [
			'foo',
			[
				ValueEnumRule::VALUES => ['foo' => 123, 'bar' => 456],
				ValueEnumRule::USE_KEYS => true,
			],
		];
	}

	/**
	 * @param mixed $value
	 * @param array<mixed> $args
	 * @param array<mixed> $expectedValues
	 *
	 * @dataProvider provideInvalidValues
	 */
	public function testProcessInvalid($value, array $args, array $expectedValues): void
	{
		$exception = null;

		try {
			$this->rule->processValue(
				$value,
				ValueEnumArgs::fromArray($this->rule->resolveArgs($args, $this->ruleArgsContext())),
				$this->fieldContext(),
			);
		} catch (ValueDoesNotMatch $exception) {
			$type = $exception->getInvalidType();
			assert($type instanceof EnumType);

			self::assertSame($expectedValues, $type->getValues());
		}

		self::assertNotNull($exception);
	}

	/**
	 * @return Generator<array<mixed>>
	 */
	public function provideInvalidValues(): Generator
	{
		yield [
			0,
			[
				ValueEnumRule::VALUES => ['foo', 'bar'],
			],
			['foo', 'bar'],
		];

		yield [
			123,
			[
				ValueEnumRule::VALUES => ['foo' => 123, 'bar' => 456],
				ValueEnumRule::USE_KEYS => true,
			],
			['foo', 'bar'],
		];
	}

	public function testType(): void
	{
		$args = ValueEnumArgs::fromArray($this->rule->resolveArgs([
			ValueEnumRule::VALUES => ['foo', 'bar'],
		], $this->ruleArgsContext()));

		$type = $this->rule->createType($args, $this->typeContext);

		self::assertEquals(
			$this->rule->createType($args, $this->fieldContext()),
			$type,
		);

		self::assertSame(['foo', 'bar'], $type->getValues());
	}

}
