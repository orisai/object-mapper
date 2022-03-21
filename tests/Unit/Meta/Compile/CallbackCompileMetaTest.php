<?php declare(strict_types = 1);

namespace Tests\Orisai\ObjectMapper\Unit\Meta\Compile;

use Orisai\ObjectMapper\Callbacks\BeforeCallback;
use Orisai\ObjectMapper\Meta\Compile\CallbackCompileMeta;
use PHPUnit\Framework\TestCase;

final class CallbackCompileMetaTest extends TestCase
{

	public function test(): void
	{
		$type = BeforeCallback::class;
		$args = ['foo' => 'bar'];
		$meta = new CallbackCompileMeta($type, $args);

		self::assertSame($type, $meta->getType());
		self::assertSame($args, $meta->getArgs());
	}

}
