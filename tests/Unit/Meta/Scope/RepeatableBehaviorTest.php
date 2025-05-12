<?php declare(strict_types = 1);

namespace Tests\Orisai\ObjectMapper\Unit\Meta\Scope;

use Orisai\ObjectMapper\Meta\Scope\RepeatableBehavior;
use PHPUnit\Framework\TestCase;

final class RepeatableBehaviorTest extends TestCase
{

	public function test(): void
	{
		self::assertSame('NoRepeat', RepeatableBehavior::noRepeat()->name);
		self::assertSame('Merge', RepeatableBehavior::merge()->name);
		self::assertSame('Override', RepeatableBehavior::override()->name);

		self::assertSame(
			[
				RepeatableBehavior::noRepeat(),
				RepeatableBehavior::merge(),
				RepeatableBehavior::override(),
			],
			RepeatableBehavior::cases(),
		);
	}

}
