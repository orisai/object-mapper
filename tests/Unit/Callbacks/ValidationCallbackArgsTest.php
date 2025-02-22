<?php declare(strict_types = 1);

namespace Tests\Orisai\ObjectMapper\Unit\Callbacks;

use Orisai\ObjectMapper\Callbacks\CallbackRuntime;
use Orisai\ObjectMapper\Callbacks\ValidationCallbackArgs;
use Orisai\ObjectMapper\Meta\Runtime\PhpMethodMeta;
use PHPUnit\Framework\TestCase;
use Tests\Orisai\ObjectMapper\Doubles\NoDefaultsVO;
use function serialize;
use function unserialize;

final class ValidationCallbackArgsTest extends TestCase
{

	public function test(): void
	{
		$meta = new PhpMethodMeta(
			NoDefaultsVO::class,
			'method',
			false,
			false,
			false,
		);

		$args = new ValidationCallbackArgs(
			CallbackRuntime::process(),
			$meta,
		);

		self::assertSame($meta, $args->meta);
		self::assertSame(CallbackRuntime::process(), $args->runtime);

		self::assertEquals(
			unserialize(serialize($args)),
			$args,
		);
	}

}
