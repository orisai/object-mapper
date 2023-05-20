<?php declare(strict_types = 1);

namespace Tests\Orisai\ObjectMapper\Unit\Rules;

use Generator;
use Orisai\ObjectMapper\Rules\ArrayEnumRule;
use Orisai\ObjectMapper\Rules\ArrayEnumValue;
use Orisai\ObjectMapper\Tester\DefinitionTester;
use PHPUnit\Framework\TestCase;
use function get_class;
use const PHP_VERSION_ID;

final class ArrayEnumValueTest extends TestCase
{

	public function test(): void
	{
		$cases = ['foo', 'bar', 'baz'];
		$definition = new ArrayEnumValue($cases);

		self::assertSame(ArrayEnumRule::class, $definition->getType());
		self::assertSame(
			[
				'cases' => $cases,
				'useKeys' => false,
				'allowUnknown' => false,
			],
			$definition->getArgs(),
		);

		DefinitionTester::assertIsRuleAnnotation(get_class($definition));
		if (PHP_VERSION_ID >= 8_00_00) {
			DefinitionTester::assertIsRuleAttribute(get_class($definition));
		}
	}

	/**
	 * @param array<mixed> $cases
	 *
	 * @dataProvider provideVariant
	 */
	public function testVariant(array $cases, bool $useKeys, bool $allowUnknown): void
	{
		$definition = new ArrayEnumValue($cases, $useKeys, $allowUnknown);

		self::assertEquals(
			[
				'cases' => $cases,
				'useKeys' => $useKeys,
				'allowUnknown' => $allowUnknown,
			],
			$definition->getArgs(),
		);
	}

	public static function provideVariant(): Generator
	{
		yield [
			['foo', 'bar'],
			false,
			true,
		];

		yield [
			[1, 2, 3],
			true,
			false,
		];
	}

}
