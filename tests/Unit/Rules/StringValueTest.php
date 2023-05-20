<?php declare(strict_types = 1);

namespace Tests\Orisai\ObjectMapper\Unit\Rules;

use Generator;
use Orisai\ObjectMapper\Rules\StringRule;
use Orisai\ObjectMapper\Rules\StringValue;
use Orisai\ObjectMapper\Tester\DefinitionTester;
use PHPUnit\Framework\TestCase;
use function get_class;
use const PHP_VERSION_ID;

final class StringValueTest extends TestCase
{

	public function test(): void
	{
		$definition = new StringValue();

		self::assertSame(StringRule::class, $definition->getType());
		self::assertSame(
			[
				'pattern' => null,
				'minLength' => null,
				'maxLength' => null,
				'notEmpty' => false,
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
	public function testVariant(?string $pattern, ?int $minLength, ?int $maxLength, bool $notEmpty): void
	{
		$definition = new StringValue($pattern, $minLength, $maxLength, $notEmpty);

		self::assertEquals(
			[
				'pattern' => $pattern,
				'minLength' => $minLength,
				'maxLength' => $maxLength,
				'notEmpty' => $notEmpty,
			],
			$definition->getArgs(),
		);
	}

	public static function provideVariant(): Generator
	{
		yield [
			null,
			null,
			null,
			false,
		];

		yield [
			'/[\s\S]/',
			10,
			20,
			true,
		];
	}

}
