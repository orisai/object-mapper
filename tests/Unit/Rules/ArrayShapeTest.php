<?php declare(strict_types = 1);

namespace Tests\Orisai\ObjectMapper\Unit\Rules;

use Orisai\ObjectMapper\Rules\ArrayShape;
use Orisai\ObjectMapper\Rules\ArrayShapeRule;
use Orisai\ObjectMapper\Rules\MixedValue;
use Orisai\ObjectMapper\Tester\DefinitionTester;
use PHPUnit\Framework\TestCase;
use function get_class;
use const PHP_VERSION_ID;

final class ArrayShapeTest extends TestCase
{

	public function test(): void
	{
		$fields = [
			'foo' => new MixedValue(),
			'bar' => new MixedValue(),
			123 => new MixedValue(),
		];

		$definition = new ArrayShape($fields);

		self::assertSame(ArrayShapeRule::class, $definition->getHandler());
		self::assertSame(
			[
				ArrayShapeRule::Fields => $fields,
			],
			$definition->getArgs(),
		);

		DefinitionTester::assertIsRuleAnnotation(get_class($definition));
		if (PHP_VERSION_ID >= 8_00_00) {
			DefinitionTester::assertIsRuleAttribute(get_class($definition));
		}
	}

}
