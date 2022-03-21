<?php declare(strict_types = 1);

namespace Tests\Orisai\ObjectMapper\Unit\Meta\Runtime;

use Orisai\ObjectMapper\Callbacks\BaseCallbackArgs;
use Orisai\ObjectMapper\Callbacks\BeforeCallback;
use Orisai\ObjectMapper\Meta\Runtime\CallbackRuntimeMeta;
use PHPUnit\Framework\TestCase;

final class CallbackRuntimeMetaTest extends TestCase
{

	public function test(): void
	{
		$type = BeforeCallback::class;
		$args = new BaseCallbackArgs('method', false, false);
		$meta = new CallbackRuntimeMeta($type, $args);

		self::assertSame($type, $meta->getType());
		self::assertSame($args, $meta->getArgs());
	}

}
