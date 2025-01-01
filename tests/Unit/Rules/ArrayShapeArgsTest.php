<?php declare(strict_types = 1);

namespace Tests\Orisai\ObjectMapper\Unit\Rules;

use Orisai\ObjectMapper\Args\EmptyArgs;
use Orisai\ObjectMapper\Meta\Runtime\RuleRuntimeMeta;
use Orisai\ObjectMapper\Rules\ArrayShapeArgs;
use Orisai\ObjectMapper\Rules\MixedRule;
use PHPUnit\Framework\TestCase;
use function serialize;
use function unserialize;

final class ArrayShapeArgsTest extends TestCase
{

	public function test(): void
	{
		$fields = [
			'foo' => new RuleRuntimeMeta(MixedRule::class, new EmptyArgs()),
			'bar' => new RuleRuntimeMeta(MixedRule::class, new EmptyArgs()),
			123 => new RuleRuntimeMeta(MixedRule::class, new EmptyArgs()),
		];

		$args = new ArrayShapeArgs($fields);

		self::assertSame($fields, $args->fields);

		self::assertEquals(
			unserialize(serialize($args)),
			$args,
		);
	}

}
