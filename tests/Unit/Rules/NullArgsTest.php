<?php declare(strict_types = 1);

namespace Tests\Orisai\ObjectMapper\Unit\Rules;

use Orisai\ObjectMapper\Rules\NullArgs;
use PHPUnit\Framework\TestCase;
use function serialize;
use function unserialize;

final class NullArgsTest extends TestCase
{

	public function test(): void
	{
		$args = new NullArgs(false);

		self::assertFalse($args->castEmptyString);
		self::assertEquals(
			unserialize(serialize($args)),
			$args,
		);
	}

	public function testVariant(): void
	{
		$args = new NullArgs(true);

		self::assertTrue($args->castEmptyString);
		self::assertEquals(
			unserialize(serialize($args)),
			$args,
		);
	}

}
