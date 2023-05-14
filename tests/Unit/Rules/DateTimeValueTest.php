<?php declare(strict_types = 1);

namespace Tests\Orisai\ObjectMapper\Unit\Rules;

use DateTimeImmutable;
use Orisai\ObjectMapper\Rules\DateTimeRule;
use Orisai\ObjectMapper\Rules\DateTimeValue;
use Orisai\ObjectMapper\Tester\DefinitionTester;
use PHPUnit\Framework\TestCase;
use function get_class;
use const PHP_VERSION_ID;

final class DateTimeValueTest extends TestCase
{

	public function test(): void
	{
		$definition = new DateTimeValue();

		self::assertSame(DateTimeRule::class, $definition->getType());
		self::assertSame(
			[
				'class' => DateTimeImmutable::class,
				'format' => 'iso_compat',
			],
			$definition->getArgs(),
		);

		DefinitionTester::assertIsRuleAnnotation(get_class($definition));
		if (PHP_VERSION_ID >= 8_00_00) {
			DefinitionTester::assertIsRuleAttribute(get_class($definition));
		}
	}

}
