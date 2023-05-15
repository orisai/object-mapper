<?php declare(strict_types = 1);

namespace Tests\Orisai\ObjectMapper\Unit\Modifiers;

use Orisai\ObjectMapper\Modifiers\FieldNameArgs;
use PHPUnit\Framework\TestCase;
use function serialize;
use function unserialize;

final class FieldNameArgsTest extends TestCase
{

	public function test(): void
	{
		$args = new FieldNameArgs('fieldName');

		self::assertSame('fieldName', $args->name);
		self::assertEquals(
			unserialize(serialize($args)),
			$args,
		);
	}

}
