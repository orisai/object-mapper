<?php declare(strict_types = 1);

namespace Tests\Orisai\ObjectMapper\Unit\Rules;

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
			],
			$definition->getArgs(),
		);

		DefinitionTester::assertIsRuleAnnotation(get_class($definition));
		if (PHP_VERSION_ID >= 8_00_00) {
			DefinitionTester::assertIsRuleAttribute(get_class($definition));
		}
	}

}
