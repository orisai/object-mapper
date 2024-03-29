<?php declare(strict_types = 1);

namespace Tests\Orisai\ObjectMapper\Unit\Rules;

use Generator;
use Orisai\ObjectMapper\Rules\NullRule;
use Orisai\ObjectMapper\Rules\NullValue;
use Orisai\ObjectMapper\Tester\DefinitionTester;
use PHPUnit\Framework\TestCase;
use function get_class;
use const PHP_VERSION_ID;

final class NullValueTest extends TestCase
{

	public function test(): void
	{
		$definition = new NullValue();

		self::assertSame(NullRule::class, $definition->getType());
		self::assertSame(
			[
				'castEmptyString' => false,
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
	public function testVariant(bool $castEmptyString): void
	{
		$definition = new NullValue($castEmptyString);

		self::assertEquals(
			[
				'castEmptyString' => $castEmptyString,
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
