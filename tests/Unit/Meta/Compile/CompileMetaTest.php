<?php declare(strict_types = 1);

namespace Tests\Orisai\ObjectMapper\Unit\Meta\Compile;

use Orisai\ObjectMapper\Callbacks\BeforeCallback;
use Orisai\ObjectMapper\Meta\Compile\CallbackCompileMeta;
use Orisai\ObjectMapper\Meta\Compile\ClassCompileMeta;
use Orisai\ObjectMapper\Meta\Compile\CompileMeta;
use Orisai\ObjectMapper\Meta\Compile\PropertyCompileMeta;
use Orisai\ObjectMapper\Meta\Compile\RuleCompileMeta;
use Orisai\ObjectMapper\Rules\MixedRule;
use Orisai\SourceMap\ClassSource;
use Orisai\SourceMap\FileSource;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

final class CompileMetaTest extends TestCase
{

	public function test(): void
	{
		$class = new ClassCompileMeta([], [], []);
		$properties = [
			'a' => new PropertyCompileMeta([], [], [], new RuleCompileMeta(MixedRule::class, [])),
		];
		$sources = [
			new ClassSource(new ReflectionClass(self::class)),
			new FileSource(__FILE__),
		];

		$meta = new CompileMeta($class, $properties, $sources);

		self::assertSame(
			$class,
			$meta->getClass(),
		);
		self::assertSame(
			$properties,
			$meta->getProperties(),
		);
		self::assertSame(
			$sources,
			$meta->getSources(),
		);
		self::assertTrue($meta->hasAnyAttributes());
	}

	public function testHasAnyAttributes(): void
	{
		$meta = new CompileMeta(
			new ClassCompileMeta([], [], []),
			[],
			[],
		);
		self::assertFalse($meta->hasAnyAttributes());

		$meta = new CompileMeta(
			new ClassCompileMeta(
				[
					new CallbackCompileMeta(BeforeCallback::class, []),
				],
				[],
				[],
			),
			[],
			[],
		);
		self::assertTrue($meta->hasAnyAttributes());

		$meta = new CompileMeta(
			new ClassCompileMeta([], [], []),
			[
				'a' => new PropertyCompileMeta([], [], [], new RuleCompileMeta(MixedRule::class, [])),
			],
			[],
		);
		self::assertTrue($meta->hasAnyAttributes());
	}

}
