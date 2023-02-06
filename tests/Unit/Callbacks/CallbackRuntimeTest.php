<?php declare(strict_types = 1);

namespace Tests\Orisai\ObjectMapper\Unit\Callbacks;

use Orisai\ObjectMapper\Callbacks\CallbackRuntime;
use PHPUnit\Framework\TestCase;
use ValueError;

final class CallbackRuntimeTest extends TestCase
{

	public function test(): void
	{
		self::assertSame('process', CallbackRuntime::process()->value);
		self::assertSame('Process', CallbackRuntime::process()->name);
		self::assertSame('processWithoutMapping', CallbackRuntime::processWithoutMapping()->value);
		self::assertSame('ProcessWithoutMapping', CallbackRuntime::processWithoutMapping()->name);
		self::assertSame('always', CallbackRuntime::always()->value);
		self::assertSame('Always', CallbackRuntime::always()->name);

		self::assertSame(
			[
				CallbackRuntime::processWithoutMapping(),
				CallbackRuntime::process(),
				CallbackRuntime::always(),
			],
			CallbackRuntime::cases(),
		);

		self::assertSame(CallbackRuntime::process(), CallbackRuntime::from('process'));
		self::assertSame(CallbackRuntime::processWithoutMapping(), CallbackRuntime::tryFrom('processWithoutMapping'));
		self::assertSame(CallbackRuntime::always(), CallbackRuntime::tryFrom('always'));

		self::assertNull(CallbackRuntime::tryFrom('non-existent'));
		$this->expectException(ValueError::class);
		CallbackRuntime::from('non-existent');
	}

}
