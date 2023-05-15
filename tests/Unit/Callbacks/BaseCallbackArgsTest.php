<?php declare(strict_types = 1);

namespace Tests\Orisai\ObjectMapper\Unit\Callbacks;

use Orisai\ObjectMapper\Callbacks\BaseCallbackArgs;
use Orisai\ObjectMapper\Callbacks\CallbackRuntime;
use PHPUnit\Framework\TestCase;
use function serialize;
use function unserialize;

final class BaseCallbackArgsTest extends TestCase
{

	public function test(): void
	{
		$args = new BaseCallbackArgs(
			'methodName',
			false,
			true,
			CallbackRuntime::process(),
		);

		self::assertSame('methodName', $args->method);
		self::assertFalse($args->isStatic);
		self::assertTrue($args->returnsValue);
		self::assertSame(CallbackRuntime::process(), $args->runtime);

		self::assertEquals(
			unserialize(serialize($args)),
			$args,
		);
	}

	public function testVariant(): void
	{
		$args = new BaseCallbackArgs(
			'differentName',
			true,
			false,
			CallbackRuntime::always(),
		);

		self::assertSame('differentName', $args->method);
		self::assertTrue($args->isStatic);
		self::assertFalse($args->returnsValue);
		self::assertSame(CallbackRuntime::always(), $args->runtime);

		self::assertEquals(
			unserialize(serialize($args)),
			$args,
		);
	}

}
