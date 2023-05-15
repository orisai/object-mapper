<?php declare(strict_types = 1);

namespace Tests\Orisai\ObjectMapper\Unit\Rules;

use Orisai\ObjectMapper\Rules\BoolArgs;
use PHPUnit\Framework\TestCase;
use function serialize;
use function unserialize;

final class BoolArgsTest extends TestCase
{

	public function test(): void
	{
		$args = new BoolArgs(false);

		self::assertFalse($args->castBoolLike);

		self::assertEquals(
			unserialize(serialize($args)),
			$args,
		);
	}

	public function testVariant(): void
	{
		$args = new BoolArgs(true);

		self::assertTrue($args->castBoolLike);

		self::assertEquals(
			unserialize(serialize($args)),
			$args,
		);
	}

}
