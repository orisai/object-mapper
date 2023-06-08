<?php declare(strict_types = 1);

namespace Tests\Orisai\ObjectMapper\Unit\Modifiers;

use Attribute;
use Doctrine\Common\Annotations\Annotation\Target;
use Orisai\ObjectMapper\Modifiers\RequiresDependencies;
use Orisai\ObjectMapper\Modifiers\RequiresDependenciesModifier;
use Orisai\ObjectMapper\Tester\DefinitionTester;
use PHPUnit\Framework\TestCase;
use Tests\Orisai\ObjectMapper\Doubles\Dependencies\DependenciesUsingVoInjector;
use function get_class;
use const PHP_VERSION_ID;

final class RequiresDependenciesTest extends TestCase
{

	public function test(): void
	{
		$definition = new RequiresDependencies(DependenciesUsingVoInjector::class);

		self::assertSame(RequiresDependenciesModifier::class, $definition->getType());
		self::assertSame(
			[
				'injector' => DependenciesUsingVoInjector::class,
			],
			$definition->getArgs(),
		);

		DefinitionTester::assertIsModifierAnnotation(
			get_class($definition),
			[Target::TARGET_CLASS],
		);
		if (PHP_VERSION_ID >= 8_00_00) {
			DefinitionTester::assertIsModifierAttribute(
				get_class($definition),
				[Attribute::TARGET_CLASS],
			);
		}
	}

}
