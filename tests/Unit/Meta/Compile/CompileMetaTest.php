<?php declare(strict_types = 1);

namespace Tests\Orisai\ObjectMapper\Unit\Meta\Compile;

use Orisai\ObjectMapper\Meta\Compile\ClassCompileMeta;
use Orisai\ObjectMapper\Meta\Compile\CompileMeta;
use Orisai\ObjectMapper\Meta\Compile\PropertyCompileMeta;
use Orisai\ObjectMapper\Meta\Compile\RuleCompileMeta;
use Orisai\ObjectMapper\Rules\MixedRule;
use PHPUnit\Framework\TestCase;

final class CompileMetaTest extends TestCase
{

	public function test(): void
	{
		$class = new ClassCompileMeta([], [], []);
		$properties = [
			'a' => new PropertyCompileMeta([], [], [], new RuleCompileMeta(MixedRule::class, [])),
		];

		$meta = new CompileMeta($class, $properties);

		self::assertSame(
			$class,
			$meta->getClass(),
		);
		self::assertSame(
			$properties,
			$meta->getProperties(),
		);
	}

}
