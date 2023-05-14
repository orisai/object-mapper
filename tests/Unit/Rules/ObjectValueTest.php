<?php declare(strict_types = 1);

namespace Tests\Orisai\ObjectMapper\Unit\Rules;

use Orisai\ObjectMapper\Rules\ObjectRule;
use Orisai\ObjectMapper\Rules\ObjectValue;
use Orisai\ObjectMapper\Tester\DefinitionTester;
use PHPUnit\Framework\TestCase;
use function get_class;
use const PHP_VERSION_ID;

final class ObjectValueTest extends TestCase
{

	public function test(): void
	{
		$definition = new ObjectValue();

		self::assertSame(ObjectRule::class, $definition->getType());
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
