<?php declare(strict_types = 1);

namespace Tests\Orisai\ObjectMapper\Unit\Rules;

use Orisai\ObjectMapper\Rules\IntRule;
use Orisai\ObjectMapper\Rules\IntValue;
use Orisai\ObjectMapper\Tester\DefinitionTester;
use PHPUnit\Framework\TestCase;
use function get_class;
use const PHP_VERSION_ID;

final class IntValueTest extends TestCase
{

	public function test(): void
	{
		$definition = new IntValue();

		self::assertSame(IntRule::class, $definition->getType());
		self::assertSame(
			[
				'min' => null,
				'max' => null,
				'unsigned' => false,
				'castNumericString' => false,
			],
			$definition->getArgs(),
		);

		DefinitionTester::assertIsRuleAnnotation(get_class($definition));
		if (PHP_VERSION_ID >= 8_00_00) {
			DefinitionTester::assertIsRuleAttribute(get_class($definition));
		}
	}

}
