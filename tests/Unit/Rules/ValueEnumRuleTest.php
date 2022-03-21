<?php declare(strict_types = 1);

namespace Tests\Orisai\ObjectMapper\Unit\Rules;

use Generator;
use Orisai\ObjectMapper\Exception\ValueDoesNotMatch;
use Orisai\ObjectMapper\Rules\ValueEnumArgs;
use Orisai\ObjectMapper\Rules\ValueEnumRule;
use Orisai\ObjectMapper\Types\EnumType;
use Tests\Orisai\ObjectMapper\Toolkit\RuleTestCase;

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
	 *
	 * @dataProvider provideValidValues
	 */
	public function testProcessValid($value, ValueEnumArgs $args): void
	{
		$processed = $this->rule->processValue(
			$value,
			$args,
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
			new ValueEnumArgs(['foo', 'bar']),
		];

		yield [
			'foo',
			new ValueEnumArgs(['foo' => 123, 'bar' => 456], true),
		];
	}

	/**
	 * @param mixed        $value
	 * @param array<mixed> $expectedValues
	 *
	 * @dataProvider provideInvalidValues
	 */
	public function testProcessInvalid($value, ValueEnumArgs $args, array $expectedValues): void
	{
		$exception = null;

		try {
			$this->rule->processValue(
				$value,
				$args,
				$this->fieldContext(),
			);
		} catch (ValueDoesNotMatch $exception) {
			$type = $exception->getInvalidType();
			self::assertInstanceOf(EnumType::class, $type);

			self::assertSame($expectedValues, $type->getValues());
			self::assertSame($value, $exception->getInvalidValue());
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
			new ValueEnumArgs(['foo', 'bar']),
			['foo', 'bar'],
		];

		yield [
			123,
			new ValueEnumArgs(['foo' => 123, 'bar' => 456], true),
			['foo', 'bar'],
		];
	}

	public function testType(): void
	{
		$args = new ValueEnumArgs(['foo', 'bar']);

		$type = $this->rule->createType($args, $this->typeContext);

		self::assertEquals(
			$this->rule->createType($args, $this->fieldContext()),
			$type,
		);

		self::assertSame(['foo', 'bar'], $type->getValues());
	}

}
