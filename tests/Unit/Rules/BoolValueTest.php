<?php declare(strict_types = 1);

namespace Tests\Orisai\ObjectMapper\Unit\Rules;

use Generator;
use Orisai\ObjectMapper\Rules\BoolRule;
use Orisai\ObjectMapper\Rules\BoolValue;
use Orisai\ObjectMapper\Tester\DefinitionTester;
use PHPUnit\Framework\TestCase;
use function get_class;
use const PHP_VERSION_ID;

final class BoolValueTest extends TestCase
{

	public function test(): void
	{
		$definition = new BoolValue();

		self::assertSame(BoolRule::class, $definition->getType());
		self::assertSame(
			[
				'castBoolLike' => false,
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
	public function testVariant(bool $castBoolLike): void
	{
		$definition = new BoolValue($castBoolLike);

		self::assertEquals(
			[
				'castBoolLike' => $castBoolLike,
			],
			$definition->getArgs(),
		);
	}

	public static function provideVariant(): Generator
	{
		yield [false];
		yield [true];
	}

}
