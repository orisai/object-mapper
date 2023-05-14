<?php declare(strict_types = 1);

namespace Tests\Orisai\ObjectMapper\Unit\Rules;

use Orisai\ObjectMapper\Meta\Compile\RuleCompileMeta;
use Orisai\ObjectMapper\Rules\AnyOf;
use Orisai\ObjectMapper\Rules\AnyOfRule;
use Orisai\ObjectMapper\Rules\IntValue;
use Orisai\ObjectMapper\Rules\StringValue;
use Orisai\ObjectMapper\Tester\DefinitionTester;
use PHPUnit\Framework\TestCase;
use function get_class;
use const PHP_VERSION_ID;

final class AnyOfTest extends TestCase
{

	public function test(): void
	{
		$sub1 = new StringValue();
		$sub2 = new IntValue();
		$definition = new AnyOf([$sub1, $sub2]);

		self::assertSame(AnyOfRule::class, $definition->getType());
		self::assertEquals(
			[
				'rules' => [
					new RuleCompileMeta($sub1->getType(), $sub1->getArgs()),
					new RuleCompileMeta($sub2->getType(), $sub2->getArgs()),
				],
			],
			$definition->getArgs(),
		);

		DefinitionTester::assertIsRuleAnnotation(get_class($definition));
		if (PHP_VERSION_ID >= 8_00_00) {
			DefinitionTester::assertIsRuleAttribute(get_class($definition));
		}
	}

}