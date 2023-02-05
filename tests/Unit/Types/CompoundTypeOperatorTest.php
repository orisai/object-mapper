<?php declare(strict_types = 1);

namespace Tests\Orisai\ObjectMapper\Unit\Types;

use Orisai\ObjectMapper\Types\CompoundTypeOperator;
use PHPUnit\Framework\TestCase;
use ValueError;

final class CompoundTypeOperatorTest extends TestCase
{

	public function test(): void
	{
		self::assertSame('&&', CompoundTypeOperator::and()->value);
		self::assertSame('And', CompoundTypeOperator::and()->name);
		self::assertSame('||', CompoundTypeOperator::or()->value);
		self::assertSame('Or', CompoundTypeOperator::or()->name);

		self::assertSame(
			[
				CompoundTypeOperator::and(),
				CompoundTypeOperator::or(),
			],
			CompoundTypeOperator::cases(),
		);

		self::assertSame(CompoundTypeOperator::and(), CompoundTypeOperator::from('&&'));
		self::assertSame(CompoundTypeOperator::or(), CompoundTypeOperator::tryFrom('||'));

		self::assertNull(CompoundTypeOperator::tryFrom('non-existent'));
		$this->expectException(ValueError::class);
		CompoundTypeOperator::from('non-existent');
	}

}
