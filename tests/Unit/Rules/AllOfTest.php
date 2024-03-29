<?php declare(strict_types = 1);

namespace Tests\Orisai\ObjectMapper\Unit\Rules;

use Orisai\Exceptions\Logic\InvalidArgument;
use Orisai\ObjectMapper\Meta\Compile\RuleCompileMeta;
use Orisai\ObjectMapper\Rules\AllOf;
use Orisai\ObjectMapper\Rules\AllOfRule;
use Orisai\ObjectMapper\Rules\IntValue;
use Orisai\ObjectMapper\Rules\StringValue;
use Orisai\ObjectMapper\Tester\DefinitionTester;
use PHPUnit\Framework\TestCase;
use stdClass;
use function get_class;
use const PHP_VERSION_ID;

final class AllOfTest extends TestCase
{

	public function test(): void
	{
		$sub1 = new StringValue();
		$sub2 = new IntValue();
		$definition = new AllOf([$sub1, $sub2]);

		self::assertSame(AllOfRule::class, $definition->getType());
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

	public function testInvalid(): void
	{
		$this->expectException(InvalidArgument::class);
		$this->expectExceptionMessage(
			<<<'MSG'
'Orisai\ObjectMapper\Rules\AllOf(definitions)' expects all values to be subtype of 'Orisai\ObjectMapper\Rules\RuleDefinition', 'stdClass' given.
MSG,
		);

		/** @phpstan-ignore-next-line */
		new AllOf([new stdClass()]);
	}

}
