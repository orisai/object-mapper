<?php declare(strict_types = 1);

namespace Tests\Orisai\ObjectMapper\Unit\Rules;

use Generator;
use Orisai\ObjectMapper\Exception\ValueDoesNotMatch;
use Orisai\ObjectMapper\Rules\ArrayEnumArgs;
use Orisai\ObjectMapper\Rules\ArrayEnumRule;
use Orisai\ObjectMapper\Types\EnumType;
use Tests\Orisai\ObjectMapper\Toolkit\ProcessingTestCase;

final class ArrayEnumRuleTest extends ProcessingTestCase
{

	private ArrayEnumRule $rule;

	protected function setUp(): void
	{
		parent::setUp();
		$this->rule = new ArrayEnumRule();
	}

	/**
	 * @param mixed $given
	 * @param mixed $expected
	 *
	 * @dataProvider provideValidValues
	 */
	public function testProcessValid($given, $expected, ArrayEnumArgs $args): void
	{
		$processed = $this->rule->processValue(
			$given,
			$args,
			$this->fieldContext(),
		);

		self::assertSame($expected, $processed);
	}

	/**
	 * @return Generator<array<mixed>>
	 */
	public function provideValidValues(): Generator
	{
		yield [
			'foo',
			'foo',
			new ArrayEnumArgs(['foo', 'bar'], false, false),
		];

		yield [
			'foo',
			'foo',
			new ArrayEnumArgs(['foo' => 123, 'bar' => 456], true, false),
		];

		yield [
			'unknown',
			null,
			new ArrayEnumArgs(['foo', 'bar'], false, true),
		];
	}

	/**
	 * @param mixed        $value
	 * @param array<mixed> $expectedCases
	 *
	 * @dataProvider provideInvalidValues
	 */
	public function testProcessInvalid($value, ArrayEnumArgs $args, array $expectedCases): void
	{
		$exception = null;

		try {
			$this->rule->processValue(
				$value,
				$args,
				$this->fieldContext(),
			);
		} catch (ValueDoesNotMatch $exception) {
			$type = $exception->getType();
			self::assertInstanceOf(EnumType::class, $type);

			self::assertSame($expectedCases, $type->getCases());
			self::assertSame($value, $exception->getValue()->get());
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
			new ArrayEnumArgs(['foo', 'bar'], false, false),
			['foo', 'bar'],
		];

		yield [
			123,
			new ArrayEnumArgs(['foo' => 123, 'bar' => 456], true, false),
			['foo', 'bar'],
		];
	}

	public function testType(): void
	{
		$args = new ArrayEnumArgs(['foo', 'bar'], false, false);

		$type = $this->rule->createType($args, $this->createTypeContext());

		self::assertEquals(
			$this->rule->createType($args, $this->fieldContext()),
			$type,
		);

		self::assertSame(['foo', 'bar'], $type->getCases());
	}

}
