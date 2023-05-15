<?php declare(strict_types = 1);

namespace Tests\Orisai\ObjectMapper\Unit\Rules;

use Orisai\ObjectMapper\Args\EmptyArgs;
use Orisai\ObjectMapper\Meta\Runtime\RuleRuntimeMeta;
use Orisai\ObjectMapper\Rules\MixedRule;
use Orisai\ObjectMapper\Rules\MultiValueArgs;
use PHPUnit\Framework\TestCase;
use function serialize;
use function unserialize;

final class MultiValueArgsTest extends TestCase
{

	public function test(): void
	{
		$item = new RuleRuntimeMeta(MixedRule::class, new EmptyArgs());
		$args = new MultiValueArgs($item, null, null, false);

		self::assertSame($item, $args->itemRuleMeta);
		self::assertNull($args->minItems);
		self::assertNull($args->maxItems);
		self::assertFalse($args->mergeDefaults);

		self::assertEquals(
			unserialize(serialize($args)),
			$args,
		);
	}

	public function testVariant(): void
	{
		$item = new RuleRuntimeMeta(MixedRule::class, new EmptyArgs());
		$args = new MultiValueArgs($item, 10, 20, true);

		self::assertSame($item, $args->itemRuleMeta);
		self::assertSame(10, $args->minItems);
		self::assertSame(20, $args->maxItems);
		self::assertTrue($args->mergeDefaults);

		self::assertEquals(
			unserialize(serialize($args)),
			$args,
		);
	}

}
