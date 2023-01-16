<?php declare(strict_types = 1);

namespace Tests\Orisai\ObjectMapper\Unit\Processing;

use Orisai\ObjectMapper\Args\EmptyArgs;
use Orisai\ObjectMapper\Meta\Runtime\ClassRuntimeMeta;
use Orisai\ObjectMapper\Meta\Runtime\ModifierRuntimeMeta;
use Orisai\ObjectMapper\Modifiers\CreateWithoutConstructorModifier;
use Orisai\ObjectMapper\Processing\DefaultObjectCreator;
use Orisai\ObjectMapper\Processing\ObjectHolder;
use PHPUnit\Framework\TestCase;
use Tests\Orisai\ObjectMapper\Doubles\Constructing\ConstructorUsingVO;
use Tests\Orisai\ObjectMapper\Doubles\DefaultsVO;

final class ObjectHolderTest extends TestCase
{

	public function testInitInstance(): void
	{
		$creator = new DefaultObjectCreator();
		$meta = new ClassRuntimeMeta([], [], []);
		$holder = new ObjectHolder($creator, $meta, DefaultsVO::class);

		self::assertSame(DefaultsVO::class, $holder->getClass());
		$instance = $holder->getInstance();
		self::assertInstanceOf(DefaultsVO::class, $instance);
		self::assertSame($instance, $holder->getInstance());
	}

	public function testGetInstance(): void
	{
		$creator = new DefaultObjectCreator();
		$meta = new ClassRuntimeMeta([], [], []);
		$vo = new DefaultsVO();
		$holder = new ObjectHolder($creator, $meta, DefaultsVO::class, $vo);

		self::assertSame(DefaultsVO::class, $holder->getClass());
		$instance = $holder->getInstance();
		self::assertSame($vo, $instance);
		self::assertSame($instance, $holder->getInstance());
	}

	public function testSkipConstructor(): void
	{
		$creator = new DefaultObjectCreator();
		$meta = new ClassRuntimeMeta([], [], [
			CreateWithoutConstructorModifier::class => new ModifierRuntimeMeta(
				CreateWithoutConstructorModifier::class,
				new EmptyArgs(),
			),
		]);
		$holder = new ObjectHolder($creator, $meta, ConstructorUsingVO::class);

		self::assertSame(ConstructorUsingVO::class, $holder->getClass());
		$instance = $holder->getInstance();
		self::assertInstanceOf(ConstructorUsingVO::class, $instance);
		self::assertSame($instance, $holder->getInstance());
	}

}
