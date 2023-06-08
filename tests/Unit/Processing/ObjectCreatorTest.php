<?php declare(strict_types = 1);

namespace Tests\Orisai\ObjectMapper\Unit\Processing;

use Orisai\ObjectMapper\Processing\DefaultDependencyInjectorManager;
use Orisai\ObjectMapper\Processing\ObjectCreator;
use PHPUnit\Framework\TestCase;
use stdClass;
use Tests\Orisai\ObjectMapper\Doubles\DefaultsVO;
use Tests\Orisai\ObjectMapper\Doubles\Dependencies\DependenciesUsingVo;
use Tests\Orisai\ObjectMapper\Doubles\Dependencies\DependenciesUsingVoInjector;

final class ObjectCreatorTest extends TestCase
{

	public function test(): void
	{
		$injectorManager = new DefaultDependencyInjectorManager();
		$creator = new ObjectCreator($injectorManager);

		$instance = $creator->createInstance(DefaultsVO::class, []);
		self::assertEquals(new DefaultsVO(), $instance);
	}

	public function testDependencies(): void
	{
		$injectorManager = new DefaultDependencyInjectorManager();
		$creator = new ObjectCreator($injectorManager);

		$dependency = new stdClass();
		$injector = new DependenciesUsingVoInjector($dependency);
		$injectorManager->addInjector($injector);

		$instance = $creator->createInstance(DependenciesUsingVo::class, [
			DependenciesUsingVoInjector::class,
		]);
		self::assertSame($dependency, $instance->dependency);
	}

}
