<?php declare(strict_types = 1);

namespace Tests\Orisai\ObjectMapper\Unit\Meta\Runtime;

use Orisai\ObjectMapper\Callbacks\BeforeValidationCallback;
use Orisai\ObjectMapper\Callbacks\CallbackRuntime;
use Orisai\ObjectMapper\Callbacks\ValidationCallbackArgs;
use Orisai\ObjectMapper\Meta\Runtime\CallbackRuntimeMeta;
use Orisai\ObjectMapper\Meta\Runtime\PhpMethodMeta;
use PHPUnit\Framework\TestCase;
use Tests\Orisai\ObjectMapper\Doubles\NoDefaultsVO;
use function serialize;
use function unserialize;

final class CallbackRuntimeMetaTest extends TestCase
{

	public function test(): void
	{
		$type = BeforeValidationCallback::class;
		$args = new ValidationCallbackArgs(
			CallbackRuntime::process(),
			new PhpMethodMeta(
				NoDefaultsVO::class,
				'method',
				false,
				false,
				false,
			),
		);
		$meta = new CallbackRuntimeMeta($type, $args);

		self::assertSame($type, $meta->type);
		self::assertSame($args, $meta->args);
		self::assertEquals($meta, unserialize(serialize($meta)));
	}

}
