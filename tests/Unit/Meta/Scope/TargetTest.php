<?php declare(strict_types = 1);

namespace Tests\Orisai\ObjectMapper\Unit\Meta\Scope;

use Orisai\ObjectMapper\Meta\Scope\Target;
use PHPUnit\Framework\TestCase;

final class TargetTest extends TestCase
{

	public function test(): void
	{
		self::assertSame('TargetClass', Target::targetClass()->name);
		self::assertSame('TargetConstant', Target::targetConstant()->name);
		self::assertSame('TargetProperty', Target::targetProperty()->name);
		self::assertSame('TargetMethod', Target::targetMethod()->name);
		self::assertSame('TargetParameter', Target::targetParameter()->name);

		self::assertSame(
			[
				Target::targetClass(),
				Target::targetConstant(),
				Target::targetProperty(),
				Target::targetMethod(),
				Target::targetParameter(),
			],
			Target::cases(),
		);
	}

}
