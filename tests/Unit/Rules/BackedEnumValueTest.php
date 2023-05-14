<?php declare(strict_types = 1);

namespace Tests\Orisai\ObjectMapper\Unit\Rules;

use Orisai\ObjectMapper\Rules\BackedEnumRule;
use Orisai\ObjectMapper\Rules\BackedEnumValue;
use Orisai\ObjectMapper\Tester\DefinitionTester;
use PHPUnit\Framework\TestCase;
use Tests\Orisai\ObjectMapper\Doubles\Enums\ExampleIntEnum;
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

}
