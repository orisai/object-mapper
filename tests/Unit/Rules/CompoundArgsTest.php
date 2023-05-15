<?php declare(strict_types = 1);

namespace Tests\Orisai\ObjectMapper\Unit\Rules;

use Orisai\ObjectMapper\Args\EmptyArgs;
use Orisai\ObjectMapper\Meta\Runtime\RuleRuntimeMeta;
use Orisai\ObjectMapper\Rules\CompoundArgs;
use Orisai\ObjectMapper\Rules\MixedRule;
use PHPUnit\Framework\TestCase;
use function serialize;
use function unserialize;

final class CompoundArgsTest extends TestCase
{

	public function test(): void
	{
		$rules = [
			new RuleRuntimeMeta(MixedRule::class, new EmptyArgs()),
			new RuleRuntimeMeta(MixedRule::class, new EmptyArgs()),
		];
		$args = new CompoundArgs($rules);

		self::assertSame($rules, $args->rules);

		self::assertEquals(
			unserialize(serialize($args)),
			$args,
		);
	}

}
