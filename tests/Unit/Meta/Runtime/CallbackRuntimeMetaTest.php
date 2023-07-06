<?php declare(strict_types = 1);

namespace Tests\Orisai\ObjectMapper\Unit\Meta\Runtime;

use Orisai\ObjectMapper\Callbacks\BaseCallbackArgs;
use Orisai\ObjectMapper\Callbacks\BeforeCallback;
use Orisai\ObjectMapper\Callbacks\CallbackRuntime;
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
		$type = BeforeCallback::class;
		$args = new BaseCallbackArgs('method', false, false, CallbackRuntime::process());
		$declaringClass = new ReflectionClass(DefaultsVO::class);
		$meta = new CallbackRuntimeMeta($type, $args, $declaringClass);

		self::assertSame($type, $meta->getType());
		self::assertSame($args, $meta->getArgs());
		self::assertSame($declaringClass, $meta->getDeclaringClass());
		self::assertEquals($meta, unserialize(serialize($meta)));
	}

}
