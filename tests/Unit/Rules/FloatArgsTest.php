<?php declare(strict_types = 1);

namespace Tests\Orisai\ObjectMapper\Unit\Rules;

use Orisai\ObjectMapper\Rules\FloatArgs;
use PHPUnit\Framework\TestCase;
use function serialize;
use function unserialize;

final class FloatArgsTest extends TestCase
{

	public function test(): void
	{
		$args = new FloatArgs(null, null, false, true);

		self::assertNull($args->min);
		self::assertNull($args->max);
		self::assertFalse($args->unsigned);
		self::assertTrue($args->castNumericString);

		self::assertEquals(
			unserialize(serialize($args)),
			$args,
		);
	}

	public function testVariant(): void
	{
		$args = new FloatArgs(10.0, 20.2, true, false);

		self::assertSame(10.0, $args->min);
		self::assertSame(20.2, $args->max);
		self::assertTrue($args->unsigned);
		self::assertFalse($args->castNumericString);

		self::assertEquals(
			unserialize(serialize($args)),
			$args,
		);
	}

}
