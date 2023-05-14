<?php declare(strict_types = 1);

namespace Tests\Orisai\ObjectMapper\Unit\Rules;

use Orisai\ObjectMapper\Meta\Compile\RuleCompileMeta;
use Orisai\ObjectMapper\Rules\ArrayOf;
use Orisai\ObjectMapper\Rules\ArrayOfRule;
use Orisai\ObjectMapper\Rules\MixedValue;
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

		self::assertSame(ArrayOfRule::class, $definition->getType());
		self::assertEquals(
			[
				'item' => new RuleCompileMeta($item->getType(), $item->getArgs()),
				'minItems' => null,
				'maxItems' => null,
				'mergeDefaults' => false,
				'key' => null,
			],
			$definition->getArgs(),
		);

		DefinitionTester::assertIsRuleAnnotation(get_class($definition));
		if (PHP_VERSION_ID >= 8_00_00) {
			DefinitionTester::assertIsRuleAttribute(get_class($definition));
		}
	}

}
