<?php declare(strict_types = 1);

namespace Tests\Orisai\ObjectMapper\Unit\Callbacks;

use Orisai\ObjectMapper\Callbacks\AfterMappingCallbackArgs;
use PHPUnit\Framework\TestCase;
use function serialize;
use function unserialize;

final class AfterMappingCallbackArgsTest extends TestCase
{

	public function test(): void
	{
		$args = new AfterMappingCallbackArgs('methodName');

		self::assertSame('methodName', $args->method);

		self::assertEquals(
			unserialize(serialize($args)),
			$args,
		);
	}

}
