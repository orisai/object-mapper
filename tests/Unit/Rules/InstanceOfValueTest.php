<?php declare(strict_types = 1);

namespace Tests\Orisai\ObjectMapper\Unit\Rules;

use Orisai\ObjectMapper\Rules\InstanceOfRule;
use Orisai\ObjectMapper\Rules\InstanceOfValue;
use Orisai\ObjectMapper\Tester\DefinitionTester;
use PHPUnit\Framework\TestCase;
use stdClass;
use function get_class;
use const PHP_VERSION_ID;

final class InstanceOfValueTest extends TestCase
{

	public function test(): void
	{
		$type = stdClass::class;
		$definition = new InstanceOfValue($type);

		self::assertSame(InstanceOfRule::class, $definition->getType());
		self::assertSame(
			[
				'type' => $type,
			],
			$definition->getArgs(),
		);

		DefinitionTester::assertIsRuleAnnotation(get_class($definition));
		if (PHP_VERSION_ID >= 8_00_00) {
			DefinitionTester::assertIsRuleAttribute(get_class($definition));
		}
	}

}
