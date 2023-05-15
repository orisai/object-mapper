<?php declare(strict_types = 1);

namespace Tests\Orisai\ObjectMapper\Unit\Rules;

use Orisai\ObjectMapper\Rules\BackedEnumArgs;
use PHPUnit\Framework\TestCase;
use Tests\Orisai\ObjectMapper\Doubles\Enums\ExampleIntEnum;
use Tests\Orisai\ObjectMapper\Doubles\Enums\ExampleStringEnum;
use function serialize;
use function unserialize;

final class BackedEnumArgsTest extends TestCase
{

	public function test(): void
	{
		$args = new BackedEnumArgs(ExampleIntEnum::class, false);

		self::assertSame(ExampleIntEnum::class, $args->class);
		self::assertFalse($args->allowUnknown);

		self::assertEquals(
			unserialize(serialize($args)),
			$args,
		);
	}

	public function testVariant(): void
	{
		$args = new BackedEnumArgs(ExampleStringEnum::class, true);

		self::assertSame(ExampleStringEnum::class, $args->class);
		self::assertTrue($args->allowUnknown);

		self::assertEquals(
			unserialize(serialize($args)),
			$args,
		);
	}

}
