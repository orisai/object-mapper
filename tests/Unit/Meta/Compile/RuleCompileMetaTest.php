<?php declare(strict_types = 1);

namespace Tests\Orisai\ObjectMapper\Unit\Meta\Compile;

use Orisai\ObjectMapper\Meta\Compile\RuleCompileMeta;
use Orisai\ObjectMapper\Rules\CompoundRule;
use Orisai\ObjectMapper\Rules\MixedRule;
use Orisai\ObjectMapper\Rules\NullRule;
use PHPUnit\Framework\TestCase;

final class RuleCompileMetaTest extends TestCase
{

	public function test(): void
	{
		$type = MixedRule::class;
		$args = ['foo' => 'bar'];
		$meta = new RuleCompileMeta($type, $args);

		self::assertSame($type, $meta->getType());
		self::assertSame($args, $meta->getArgs());

		self::assertFalse($meta->containsAnyOfRules([NullRule::class]));
		self::assertTrue($meta->containsAnyOfRules([MixedRule::class]));
		self::assertTrue($meta->containsAnyOfRules([MixedRule::class, NullRule::class]));

		$compound = new RuleCompileMeta(CompoundRule::class, [
			CompoundRule::Rules => [
				$meta,
			],
		]);
		self::assertFalse($compound->containsAnyOfRules([NullRule::class]));
		self::assertTrue($compound->containsAnyOfRules([MixedRule::class]));
		self::assertTrue($compound->containsAnyOfRules([MixedRule::class, NullRule::class]));
	}

}
