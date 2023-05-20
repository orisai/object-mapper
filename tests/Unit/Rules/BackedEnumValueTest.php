<?php declare(strict_types = 1);

namespace Tests\Orisai\ObjectMapper\Unit\Rules;

use BackedEnum;
use Generator;
use Orisai\ObjectMapper\Rules\BackedEnumRule;
use Orisai\ObjectMapper\Rules\BackedEnumValue;
use Orisai\ObjectMapper\Tester\DefinitionTester;
use PHPUnit\Framework\TestCase;
use Tests\Orisai\ObjectMapper\Doubles\Enums\ExampleIntEnum;
use Tests\Orisai\ObjectMapper\Doubles\Enums\ExampleStringEnum;
use function get_class;
use const PHP_VERSION_ID;

final class BackedEnumValueTest extends TestCase
{

	public function test(): void
	{
		if (PHP_VERSION_ID < 8_00_00) {
			self::markTestSkipped('Backed enum is available on PHP 8.0+');
		}

		$definition = new BackedEnumValue(ExampleIntEnum::class);

		self::assertSame(BackedEnumRule::class, $definition->getType());
		self::assertSame(
			[
				'class' => ExampleIntEnum::class,
				'allowUnknown' => false,
			],
			$definition->getArgs(),
		);

		DefinitionTester::assertIsRuleAttribute(get_class($definition));
	}

	/**
	 * @param class-string<BackedEnum> $class
	 *
	 * @dataProvider provideVariant
	 */
	public function testVariant(string $class, bool $allowUnknown): void
	{
		$definition = new BackedEnumValue($class, $allowUnknown);

		self::assertEquals(
			[
				'class' => $class,
				'allowUnknown' => $allowUnknown,
			],
			$definition->getArgs(),
		);
	}

	public static function provideVariant(): Generator
	{
		yield [
			ExampleIntEnum::class,
			false,
		];

		yield [
			ExampleStringEnum::class,
			true,
		];
	}

}
