<?php declare(strict_types = 1);

namespace Tests\Orisai\ObjectMapper\Unit\Meta\Runtime;

use Orisai\ObjectMapper\Args\EmptyArgs;
use Orisai\ObjectMapper\Meta\Runtime\RuleRuntimeMeta;
use Orisai\ObjectMapper\Rules\MixedRule;
use PHPUnit\Framework\TestCase;
use function serialize;
use function unserialize;

final class RuleRuntimeMetaTest extends TestCase
{

	public function test(): void
	{
		$type = MixedRule::class;
		$args = new EmptyArgs();
		$meta = new RuleRuntimeMeta($type, $args);

		self::assertSame($type, $meta->getType());
		self::assertSame($args, $meta->getArgs());
		self::assertEquals($meta, unserialize(serialize($meta)));
	}

}
