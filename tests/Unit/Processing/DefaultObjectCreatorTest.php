<?php declare(strict_types = 1);

namespace Tests\Orisai\ObjectMapper\Unit\Processing;

use Orisai\Exceptions\Logic\InvalidState;
use Orisai\ObjectMapper\Processing\DefaultObjectCreator;
use PHPUnit\Framework\TestCase;
use Tests\Orisai\ObjectMapper\Doubles\ConstructorUsingVO;
use Tests\Orisai\ObjectMapper\Doubles\DependentVO;
use Tests\Orisai\ObjectMapper\Doubles\EmptyVO;

final class DefaultObjectCreatorTest extends TestCase
{

	public function testCreate(): void
	{
		$creator = new DefaultObjectCreator();

		$instance = $creator->createInstance(EmptyVO::class, true);
		self::assertInstanceOf(EmptyVO::class, $instance);
	}

	public function testFailure(): void
	{
		$this->expectException(InvalidState::class);
		$this->expectExceptionMessage(
			<<<'MSG'
Context: Creating instance of class
         'Tests\Orisai\ObjectMapper\Doubles\DependentVO' via
         Orisai\ObjectMapper\Processing\DefaultObjectCreator.
Problem: Class has required constructor arguments and could not be created.
Solution: Use another 'Orisai\ObjectMapper\Processing\ObjectCreator'
          implementation or skip constructor with
          'Orisai\ObjectMapper\Attributes\Modifiers\CreateWithoutConstructor'.
MSG,
		);

		$creator = new DefaultObjectCreator();

		$creator->createInstance(DependentVO::class, true);
	}

	public function testDontUseConstructor(): void
	{
		$vo = new ConstructorUsingVO('string');
		self::assertTrue($vo->isInitialized('string'));
		self::assertSame('string', $vo->string);

		$creator = new DefaultObjectCreator();
		$vo = $creator->createInstance(ConstructorUsingVO::class, false);
		self::assertFalse($vo->isInitialized('string'));
	}

}
