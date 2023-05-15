<?php declare(strict_types = 1);

namespace Tests\Orisai\ObjectMapper\Unit\Rules;

use Orisai\ObjectMapper\Rules\MappedObjectArgs;
use PHPUnit\Framework\TestCase;
use Tests\Orisai\ObjectMapper\Doubles\DefaultsVO;
use function serialize;
use function unserialize;

final class MappedObjectArgsTest extends TestCase
{

	public function test(): void
	{
		$args = new MappedObjectArgs(DefaultsVO::class);

		self::assertSame(DefaultsVO::class, $args->class);

		self::assertEquals(
			unserialize(serialize($args)),
			$args,
		);
	}

}
