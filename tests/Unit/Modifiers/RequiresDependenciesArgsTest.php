<?php declare(strict_types = 1);

namespace Tests\Orisai\ObjectMapper\Unit\Modifiers;

use Orisai\ObjectMapper\Modifiers\RequiresDependenciesArgs;
use PHPUnit\Framework\TestCase;
use Tests\Orisai\ObjectMapper\Doubles\Dependencies\DependenciesUsingVoInjector;
use function serialize;
use function unserialize;

final class RequiresDependenciesArgsTest extends TestCase
{

	public function test(): void
	{
		$injector = DependenciesUsingVoInjector::class;
		$args = new RequiresDependenciesArgs($injector);

		self::assertSame($injector, $args->injector);
		self::assertEquals(
			unserialize(serialize($args)),
			$args,
		);
	}

}
