<?php declare(strict_types = 1);

namespace Tests\Orisai\ObjectMapper\Unit\Meta\Compile;

use Orisai\ObjectMapper\Meta\Compile\RuleCompileMeta;
use Orisai\ObjectMapper\Rules\MixedRule;
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
	}

}
