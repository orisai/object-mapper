<?php declare(strict_types = 1);

namespace Tests\Orisai\ObjectMapper\Unit\Processing;

use Orisai\Exceptions\Logic\InvalidState;
use Orisai\ObjectMapper\Processing\DefaultDependencyInjectorManager;
use PHPUnit\Framework\TestCase;
use stdClass;
use Tests\Orisai\ObjectMapper\Doubles\Dependencies\DependenciesUsingVoInjector;

final class DefaultDependencyInjectorManagerTest extends TestCase
{

	public function test(): void
	{
		$manager = new DefaultDependencyInjectorManager();

		$injector = new DependenciesUsingVoInjector(new stdClass());
		$manager->addInjector($injector);

		self::assertSame(
			$injector,
			$manager->get(DependenciesUsingVoInjector::class),
		);
	}

	public function testException(): void
	{
		$manager = new DefaultDependencyInjectorManager();

		$this->expectException(InvalidState::class);
		$this->expectExceptionMessage(
			"Injector 'Tests\Orisai\ObjectMapper\Doubles\Dependencies\DependenciesUsingVoInjector' does not exist.",
		);

		$manager->get(DependenciesUsingVoInjector::class);
	}

}
