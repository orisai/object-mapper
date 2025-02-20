<?php declare(strict_types = 1);

namespace Tests\Orisai\ObjectMapper\Unit\Meta\Runtime;

use Orisai\ObjectMapper\Callbacks\BeforeValidationCallback;
use Orisai\ObjectMapper\Callbacks\CallbackRuntime;
use Orisai\ObjectMapper\Callbacks\ValidationCallbackArgs;
use Orisai\ObjectMapper\Meta\Runtime\CallbackRuntimeMeta;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use Tests\Orisai\ObjectMapper\Doubles\DefaultsVO;
use function serialize;
use function unserialize;

final class CallbackRuntimeMetaTest extends TestCase
{

	public function test(): void
	{
		$type = BeforeValidationCallback::class;
		$args = new ValidationCallbackArgs('method', false, false, CallbackRuntime::process());
		$declaringClass = new ReflectionClass(DefaultsVO::class);
		$meta = new CallbackRuntimeMeta($type, $args, $declaringClass);

		self::assertSame($type, $meta->type);
		self::assertSame($args, $meta->args);
		self::assertSame($declaringClass, $meta->declaringClass);
		self::assertEquals($meta, unserialize(serialize($meta)));
	}

}
