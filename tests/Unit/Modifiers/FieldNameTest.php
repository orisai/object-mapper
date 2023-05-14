<?php declare(strict_types = 1);

namespace Tests\Orisai\ObjectMapper\Unit\Modifiers;

use Attribute;
use Doctrine\Common\Annotations\Annotation\Target;
use Orisai\ObjectMapper\Modifiers\FieldName;
use Orisai\ObjectMapper\Modifiers\FieldNameModifier;
use Orisai\ObjectMapper\Tester\DefinitionTester;
use PHPUnit\Framework\TestCase;
use function get_class;
use const PHP_VERSION_ID;

final class FieldNameTest extends TestCase
{

	public function test(): void
	{
		$name = 'fieldName';
		$definition = new FieldName($name);

		self::assertSame(FieldNameModifier::class, $definition->getType());
		self::assertSame(
			[
				'name' => $name,
			],
			$definition->getArgs(),
		);

		DefinitionTester::assertIsModifierAnnotation(
			get_class($definition),
			[Target::TARGET_PROPERTY],
		);
		if (PHP_VERSION_ID >= 8_00_00) {
			DefinitionTester::assertIsModifierAttribute(
				get_class($definition),
				[Attribute::TARGET_PROPERTY],
			);
		}
	}

}
