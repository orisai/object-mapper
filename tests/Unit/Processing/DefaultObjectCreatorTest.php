<?php declare(strict_types = 1);

namespace Tests\Orisai\ObjectMapper\Unit\Processing;

use ArgumentCountError;
use Orisai\Exceptions\Logic\InvalidState;
use Orisai\ObjectMapper\MappedObject;
use Orisai\ObjectMapper\Processing\DefaultObjectCreator;
use PHPUnit\Framework\TestCase;
use ReflectionProperty;
use Tests\Orisai\ObjectMapper\Doubles\Constructing\ConstructorUsingVO;
use Tests\Orisai\ObjectMapper\Doubles\Constructing\DependentVO;
use Tests\Orisai\ObjectMapper\Doubles\EmptyVO;

final class DefaultObjectCreatorTest extends TestCase
{

	public function testCreate(): void
	{
		$creator = new DefaultObjectCreator();

		// Just checking it does not fail
		$creator->checkClassIsInstantiable(EmptyVO::class, true);

		$instance = $creator->createInstance(EmptyVO::class, true);
		self::assertInstanceOf(EmptyVO::class, $instance);
	}

	public function testFailure(): void
	{
		$this->expectException(InvalidState::class);
		$this->expectExceptionMessage(
			<<<'MSG'
Context: Creating instance of class
         'Tests\Orisai\ObjectMapper\Doubles\Constructing\DependentVO' via
         Orisai\ObjectMapper\Processing\DefaultObjectCreator.
Problem: Class has required constructor arguments and could not be created.
Solution: Use another 'Orisai\ObjectMapper\Processing\ObjectCreator'
          implementation or skip constructor with
          'Orisai\ObjectMapper\Attributes\Modifiers\CreateWithoutConstructor'.
MSG,
		);

		$creator = new DefaultObjectCreator();
		$creator->checkClassIsInstantiable(DependentVO::class, true);
	}

	public function testRuntimeFailure(): void
	{
		$this->expectException(ArgumentCountError::class);

		$creator = new DefaultObjectCreator();
		$creator->createInstance(DependentVO::class, true);
	}

	public function testDontUseConstructor(): void
	{
		$vo = new ConstructorUsingVO('string');
		self::assertTrue($this->isInitialized($vo, 'string'));
		self::assertSame('string', $vo->string);

		$creator = new DefaultObjectCreator();

		// Just checking it does not fail
		$creator->checkClassIsInstantiable(EmptyVO::class, true);

		$vo = $creator->createInstance(ConstructorUsingVO::class, false);
		self::assertFalse($this->isInitialized($vo, 'string'));
	}

	private function isInitialized(MappedObject $object, string $property): bool
	{
		return (new ReflectionProperty($object, $property))->isInitialized($object);
	}

}
