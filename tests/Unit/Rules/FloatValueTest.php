<?php declare(strict_types = 1);

namespace Tests\Orisai\ObjectMapper\Unit\Rules;

use Generator;
use Orisai\ObjectMapper\Rules\FloatRule;
use Orisai\ObjectMapper\Rules\FloatValue;
use Orisai\ObjectMapper\Tester\DefinitionTester;
use PHPUnit\Framework\TestCase;
use function get_class;
use const PHP_VERSION_ID;

final class FloatValueTest extends TestCase
{

	public function test(): void
	{
		$definition = new FloatValue();

		self::assertSame(FloatRule::class, $definition->getType());
		self::assertSame(
			[
				'min' => null,
				'max' => null,
				'unsigned' => false,
				'castNumericString' => false,
			],
			$definition->getArgs(),
		);

		DefinitionTester::assertIsRuleAnnotation(get_class($definition));
		if (PHP_VERSION_ID >= 8_00_00) {
			DefinitionTester::assertIsRuleAttribute(get_class($definition));
		}
	}

	/**
	 * @dataProvider provideVariant
	 */
	public function testVariant(?float $min, ?float $max, bool $unsigned, bool $castNumericString): void
	{
		$definition = new FloatValue($min, $max, $unsigned, $castNumericString);

		self::assertSame(
			[
				'min' => $min,
				'max' => $max,
				'unsigned' => $unsigned,
				'castNumericString' => $castNumericString,
			],
			$definition->getArgs(),
		);
	}

	public static function provideVariant(): Generator
	{
		yield [null, null, false, false];

		yield [10.0, 20.2, false, true];

		yield [10.1, 20.0, true, false];
	}

}
