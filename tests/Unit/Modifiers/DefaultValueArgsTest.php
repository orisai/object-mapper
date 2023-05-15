<?php declare(strict_types = 1);

namespace Tests\Orisai\ObjectMapper\Unit\Modifiers;

use Orisai\ObjectMapper\Modifiers\DefaultValueArgs;
use PHPUnit\Framework\TestCase;
use function serialize;
use function unserialize;

final class DefaultValueArgsTest extends TestCase
{

	public function test(): void
	{
		$args = new DefaultValueArgs('value');

		self::assertSame('value', $args->value);
		self::assertEquals(
			unserialize(serialize($args)),
			$args,
		);
	}

}
