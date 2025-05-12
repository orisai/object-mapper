<?php declare(strict_types = 1);

namespace Tests\Orisai\ObjectMapper\Unit\Rules;

use Generator;
use Orisai\ObjectMapper\Rules\ArrayOf;
use Orisai\ObjectMapper\Rules\ArrayOfRule;
use Orisai\ObjectMapper\Rules\IntValue;
use Orisai\ObjectMapper\Rules\MixedValue;
use Orisai\ObjectMapper\Rules\RuleDefinition;
use Orisai\ObjectMapper\Rules\StringValue;
use Orisai\ObjectMapper\Tester\DefinitionTester;
use PHPUnit\Framework\TestCase;
use function get_class;
use const PHP_VERSION_ID;

final class ArrayOfTest extends TestCase
{

	public function test(): void
	{
		$item = new MixedValue();
		$definition = new ArrayOf($item);

		self::assertSame(ArrayOfRule::class, $definition->getHandler());
		self::assertSame(
			[
				ArrayOfRule::ItemRule => $item,
				ArrayOfRule::MinItems => null,
				ArrayOfRule::MaxItems => null,
				ArrayOfRule::MergeDefaults => false,
				ArrayOfRule::KeyRule => null,
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
	public function testVariant(
		RuleDefinition $item,
		?RuleDefinition $key,
		?int $minItems,
		?int $maxItems,
		bool $mergeDefaults
	): void
	{
		$definition = new ArrayOf($item, $key, $minItems, $maxItems, $mergeDefaults);

		self::assertSame(
			[
				ArrayOfRule::ItemRule => $item,
				ArrayOfRule::MinItems => $minItems,
				ArrayOfRule::MaxItems => $maxItems,
				ArrayOfRule::MergeDefaults => $mergeDefaults,
				ArrayOfRule::KeyRule => $key,
			],
			$definition->getArgs(),
		);
	}

	public static function provideVariant(): Generator
	{
		yield [
			new MixedValue(),
			null,
			null,
			null,
			false,
		];

		yield [
			new StringValue(),
			new IntValue(),
			10,
			20,
			true,
		];
	}

}
