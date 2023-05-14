<?php declare(strict_types = 1);

namespace Tests\Orisai\ObjectMapper\Unit\Rules;

use Orisai\ObjectMapper\Rules\ScalarRule;
use Orisai\ObjectMapper\Rules\ScalarValue;
use Orisai\ObjectMapper\Tester\DefinitionTester;
use PHPUnit\Framework\TestCase;
use function get_class;
use const PHP_VERSION_ID;

final class ScalarValueTest extends TestCase
{

	public function test(): void
	{
		$definition = new ScalarValue();

		self::assertSame(ScalarRule::class, $definition->getType());
		self::assertSame(
			[],
			$definition->getArgs(),
		);

		DefinitionTester::assertIsRuleAnnotation(get_class($definition));
		if (PHP_VERSION_ID >= 8_00_00) {
			DefinitionTester::assertIsRuleAttribute(get_class($definition));
		}
	}

}
