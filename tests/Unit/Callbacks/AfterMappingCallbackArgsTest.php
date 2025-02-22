<?php declare(strict_types = 1);

namespace Tests\Orisai\ObjectMapper\Unit\Callbacks;

use Orisai\ObjectMapper\Callbacks\AfterMappingCallbackArgs;
use Orisai\ObjectMapper\Meta\Runtime\PhpMethodMeta;
use PHPUnit\Framework\TestCase;
use Tests\Orisai\ObjectMapper\Doubles\NoDefaultsVO;
use function serialize;
use function unserialize;

final class AfterMappingCallbackArgsTest extends TestCase
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
		$args = new AfterMappingCallbackArgs($meta);

		self::assertSame($meta, $args->meta);

		self::assertEquals(
			unserialize(serialize($args)),
			$args,
		);
	}

}
