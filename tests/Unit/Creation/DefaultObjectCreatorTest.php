<?php declare(strict_types = 1);

namespace Tests\Orisai\ObjectMapper\Unit\Creation;

use Orisai\Exceptions\Logic\InvalidState;
use Orisai\ObjectMapper\Creation\DefaultObjectCreator;
use Orisai\ObjectMapper\Creation\ObjectCreator;
use PHPUnit\Framework\TestCase;
use Tests\Orisai\ObjectMapper\Doubles\DependentVO;
use Tests\Orisai\ObjectMapper\Doubles\EmptyVO;
use function sprintf;

final class DefaultObjectCreatorTest extends TestCase
{

	public function testCreate(): void
	{
		$creator = new DefaultObjectCreator();

		$instance = $creator->createInstance(EmptyVO::class);
		self::assertInstanceOf(EmptyVO::class, $instance);
	}

	public function testFailure(): void
	{
		$this->expectException(InvalidState::class);
		$this->expectExceptionMessage(sprintf(
			'%s is unable to create object with required constructor arguments. You may want use some other %s implementation.',
			DefaultObjectCreator::class,
			ObjectCreator::class,
		));

		$creator = new DefaultObjectCreator();

		$creator->createInstance(DependentVO::class);
	}

}
