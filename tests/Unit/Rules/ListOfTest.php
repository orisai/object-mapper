<?php declare(strict_types = 1);

namespace Tests\Orisai\ObjectMapper\Unit\Rules;

use Orisai\ObjectMapper\Meta\Compile\RuleCompileMeta;
use Orisai\ObjectMapper\Rules\ListOf;
use Orisai\ObjectMapper\Rules\ListOfRule;
use Orisai\ObjectMapper\Rules\MixedValue;
use Orisai\ObjectMapper\Tester\DefinitionTester;
use PHPUnit\Framework\TestCase;
use function get_class;
use const PHP_VERSION_ID;

final class ListOfTest extends TestCase
{

	public function test(): void
	{
		$item = new MixedValue();
		$definition = new ListOf($item);

		self::assertSame(ListOfRule::class, $definition->getType());
		self::assertEquals(
			[
				'item' => new RuleCompileMeta($item->getType(), $item->getArgs()),
				'minItems' => null,
				'maxItems' => null,
				'mergeDefaults' => false,
			],
			$definition->getArgs(),
		);

		DefinitionTester::assertIsRuleAnnotation(get_class($definition));
		if (PHP_VERSION_ID >= 8_00_00) {
			DefinitionTester::assertIsRuleAttribute(get_class($definition));
		}
	}

}
