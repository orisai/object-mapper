<?php declare(strict_types = 1);

namespace Tests\Orisai\ObjectMapper\Unit\Rules;

use BackedEnum;
use Generator;
use Orisai\ObjectMapper\Exception\ValueDoesNotMatch;
use Orisai\ObjectMapper\Rules\BackedEnumArgs;
use Orisai\ObjectMapper\Rules\BackedEnumRule;
use Orisai\ObjectMapper\Types\EnumType;
use Tests\Orisai\ObjectMapper\Doubles\Enums\ExampleIntEnum;
use Tests\Orisai\ObjectMapper\Doubles\Enums\ExampleStringEnum;
use Tests\Orisai\ObjectMapper\Toolkit\ProcessingTestCase;

/**
 * @requires PHP >= 8.1
 */
final class BackedEnumRuleTest extends ProcessingTestCase
{

	private BackedEnumRule $rule;

	protected function setUp(): void
	{
		parent::setUp();
		$this->rule = new BackedEnumRule();
	}

	/**
	 * @param mixed $value
	 *
	 * @dataProvider provideValidValues
	 */
	public function testProcessValid($value, ?BackedEnum $expected, BackedEnumArgs $args): void
	{
		$processed = $this->rule->processValue(
			$value,
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
			0,
			ExampleIntEnum::Foo,
			new BackedEnumArgs(ExampleIntEnum::class, false),
		];

		yield [
			'foo',
			ExampleStringEnum::Foo,
			new BackedEnumArgs(ExampleStringEnum::class, false),
		];

		yield [
			'baz',
			null,
			new BackedEnumArgs(ExampleStringEnum::class, true),
		];
	}

	/**
	 * @param mixed        $value
	 * @param array<mixed> $expectedCases
	 *
	 * @dataProvider provideInvalidValues
	 */
	public function testProcessInvalid($value, BackedEnumArgs $args, array $expectedCases): void
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
			new BackedEnumArgs(ExampleStringEnum::class, false),
			['foo', 'bar'],
		];

		yield [
			123,
			new BackedEnumArgs(ExampleIntEnum::class, false),
			[0, 1],
		];

		yield [
			'string',
			new BackedEnumArgs(ExampleIntEnum::class, false),
			[0, 1],
		];
	}

	public function testType(): void
	{
		$args = new BackedEnumArgs(ExampleStringEnum::class, false);

		$type = $this->rule->createType($args, $this->createTypeContext());

		self::assertEquals(
			$this->rule->createType($args, $this->fieldContext()),
			$type,
		);

		self::assertSame(['foo', 'bar'], $type->getCases());
	}

}
