<?php declare(strict_types = 1);

namespace Tests\Orisai\ObjectMapper\Unit\Rules;

use Orisai\ObjectMapper\Rules\ArrayEnumArgs;
use PHPUnit\Framework\TestCase;
use function serialize;
use function unserialize;

final class ArrayEnumArgsTest extends TestCase
{

	public function test(): void
	{
		$args = new ArrayEnumArgs(['foo', 'bar'], false, true);

		self::assertSame(['foo', 'bar'], $args->cases);
		self::assertFalse($args->useKeys);
		self::assertTrue($args->allowUnknown);
		self::assertEquals(
			unserialize(serialize($args)),
			$args,
		);
	}

	public function testVariant(): void
	{
		$args = new ArrayEnumArgs(['bar', 'baz'], true, false);

		self::assertSame(['bar', 'baz'], $args->cases);
		self::assertTrue($args->useKeys);
		self::assertFalse($args->allowUnknown);
		self::assertEquals(
			unserialize(serialize($args)),
			$args,
		);
	}

}
