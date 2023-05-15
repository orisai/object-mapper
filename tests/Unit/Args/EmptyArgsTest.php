<?php declare(strict_types = 1);

namespace Tests\Orisai\ObjectMapper\Unit\Args;

use Orisai\ObjectMapper\Args\EmptyArgs;
use PHPUnit\Framework\TestCase;
use function serialize;
use function unserialize;

final class EmptyArgsTest extends TestCase
{

	public function test(): void
	{
		$args = new EmptyArgs();

		self::assertEquals(
			unserialize(serialize($args)),
			$args,
		);
	}

}
