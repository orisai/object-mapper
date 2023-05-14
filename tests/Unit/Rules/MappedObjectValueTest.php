<?php declare(strict_types = 1);

namespace Tests\Orisai\ObjectMapper\Unit\Rules;

use Orisai\ObjectMapper\Rules\MappedObjectRule;
use Orisai\ObjectMapper\Rules\MappedObjectValue;
use Orisai\ObjectMapper\Tester\DefinitionTester;
use PHPUnit\Framework\TestCase;
use Tests\Orisai\ObjectMapper\Doubles\DefaultsVO;
use function get_class;
use const PHP_VERSION_ID;

final class MappedObjectValueTest extends TestCase
{

	public function test(): void
	{
		$definition = new MappedObjectValue(DefaultsVO::class);

		self::assertSame(MappedObjectRule::class, $definition->getType());
		self::assertSame(
			[
				'class' => DefaultsVO::class,
			],
			$definition->getArgs(),
		);

		DefinitionTester::assertIsRuleAnnotation(get_class($definition));
		if (PHP_VERSION_ID >= 8_00_00) {
			DefinitionTester::assertIsRuleAttribute(get_class($definition));
		}
	}

}
