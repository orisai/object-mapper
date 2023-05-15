<?php declare(strict_types = 1);

namespace Tests\Orisai\ObjectMapper\Unit\Rules;

use Orisai\ObjectMapper\Rules\InstanceOfArgs;
use PHPUnit\Framework\TestCase;
use stdClass;
use function serialize;
use function unserialize;

final class InstanceOfArgsTest extends TestCase
{

	public function test(): void
	{
		$args = new InstanceOfArgs(stdClass::class);

		self::assertSame(stdClass::class, $args->type);

		self::assertEquals(
			unserialize(serialize($args)),
			$args,
		);
	}

}
