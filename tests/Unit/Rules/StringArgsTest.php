<?php declare(strict_types = 1);

namespace Tests\Orisai\ObjectMapper\Unit\Rules;

use Orisai\ObjectMapper\Rules\StringArgs;
use PHPUnit\Framework\TestCase;
use function serialize;
use function unserialize;

final class StringArgsTest extends TestCase
{

	public function test(): void
	{
		$args = new StringArgs(null, false, null, null, true);

		self::assertNull($args->pattern);
		self::assertFalse($args->notEmpty);
		self::assertNull($args->minLength);
		self::assertNull($args->maxLength);
		self::assertTrue($args->trim);

		self::assertEquals(
			unserialize(serialize($args)),
			$args,
		);
	}

	public function testVariant(): void
	{
		$args = new StringArgs('/[\s\S]/', true, 10, 20, false);

		self::assertSame('/[\s\S]/', $args->pattern);
		self::assertTrue($args->notEmpty);
		self::assertSame(10, $args->minLength);
		self::assertSame(20, $args->maxLength);
		self::assertFalse($args->trim);

		self::assertEquals(
			unserialize(serialize($args)),
			$args,
		);
	}

}
