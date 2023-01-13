<?php declare(strict_types = 1);

namespace Tests\Orisai\ObjectMapper\Unit\Meta\Runtime;

use Orisai\ObjectMapper\Callbacks\BaseCallbackArgs;
use Orisai\ObjectMapper\Callbacks\BeforeCallback;
use Orisai\ObjectMapper\Meta\Runtime\CallbackRuntimeMeta;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

final class CallbackRuntimeMetaTest extends TestCase
{

	public function test(): void
	{
		$type = BeforeCallback::class;
		$args = new BaseCallbackArgs('method', false, false);
		$declaringClass = new ReflectionClass(self::class);
		$meta = new CallbackRuntimeMeta($type, $args, $declaringClass);

		self::assertSame($type, $meta->getType());
		self::assertSame($args, $meta->getArgs());
		self::assertSame($declaringClass, $meta->getDeclaringClass());
	}

}
