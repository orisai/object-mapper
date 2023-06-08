<?php declare(strict_types = 1);

namespace Tests\Orisai\ObjectMapper\Unit\Processing;

use Orisai\ObjectMapper\Meta\Runtime\ClassRuntimeMeta;
use Orisai\ObjectMapper\Meta\Runtime\ModifierRuntimeMeta;
use Orisai\ObjectMapper\Modifiers\RequiresDependenciesArgs;
use Orisai\ObjectMapper\Modifiers\RequiresDependenciesModifier;
use Orisai\ObjectMapper\Processing\DefaultDependencyInjectorManager;
use Orisai\ObjectMapper\Processing\ObjectCreator;
use Orisai\ObjectMapper\Processing\ObjectHolder;
use PHPUnit\Framework\TestCase;
use stdClass;
use Tests\Orisai\ObjectMapper\Doubles\DefaultsVO;
use Tests\Orisai\ObjectMapper\Doubles\Dependencies\DependentBaseVoInjector;
use Tests\Orisai\ObjectMapper\Doubles\Dependencies\DependentChildVO;
use Tests\Orisai\ObjectMapper\Doubles\Dependencies\DependentChildVoInjector1;
use Tests\Orisai\ObjectMapper\Doubles\Dependencies\DependentChildVoInjector2;

final class ObjectHolderTest extends TestCase
{

	public function testInitInstance(): void
	{
		$creator = new ObjectCreator(new DefaultDependencyInjectorManager());
		$meta = new ClassRuntimeMeta([], [], []);
		$holder = new ObjectHolder($creator, DefaultsVO::class, $meta);

		self::assertSame(DefaultsVO::class, $holder->getClass());
		$instance = $holder->getInstance();
		self::assertInstanceOf(DefaultsVO::class, $instance);
		self::assertSame($instance, $holder->getInstance());
	}

	public function testGetInstance(): void
	{
		$creator = new ObjectCreator(new DefaultDependencyInjectorManager());
		$meta = new ClassRuntimeMeta([], [], []);
		$vo = new DefaultsVO();
		$holder = new ObjectHolder($creator, DefaultsVO::class, $meta, $vo);

		self::assertSame(DefaultsVO::class, $holder->getClass());
		$instance = $holder->getInstance();
		self::assertSame($vo, $instance);
		self::assertSame($instance, $holder->getInstance());
	}

	public function testInjectDependencies(): void
	{
		$manager = new DefaultDependencyInjectorManager();
		$manager->addInjector(new DependentBaseVoInjector(new stdClass()));
		$manager->addInjector(new DependentChildVoInjector1('string'));
		$manager->addInjector(new DependentChildVoInjector2(123));

		$modifiers = [
			RequiresDependenciesModifier::class => [
				new ModifierRuntimeMeta(
					RequiresDependenciesModifier::class,
					new RequiresDependenciesArgs(DependentBaseVoInjector::class),
				),
				new ModifierRuntimeMeta(
					RequiresDependenciesModifier::class,
					new RequiresDependenciesArgs(DependentChildVoInjector1::class),
				),
				new ModifierRuntimeMeta(
					RequiresDependenciesModifier::class,
					new RequiresDependenciesArgs(DependentChildVoInjector2::class),
				),
			],
		];

		$creator = new ObjectCreator($manager);
		$meta = new ClassRuntimeMeta([], [], $modifiers);
		$holder = new ObjectHolder($creator, DependentChildVO::class, $meta);

		self::assertSame(DependentChildVO::class, $holder->getClass());
		$instance = $holder->getInstance();
		self::assertSame($instance, $holder->getInstance());
		self::assertEquals(
			new DependentChildVO(
				new stdClass(),
				'string',
				123,
			),
			$instance,
		);
	}

}
