<?php declare(strict_types = 1);

namespace Tests\Orisai\ObjectMapper\Unit\Meta\Compile;

use Orisai\ObjectMapper\Callbacks\BeforeCallback;
use Orisai\ObjectMapper\Meta\Compile\CallbackCompileMeta;
use Orisai\ObjectMapper\Meta\Compile\ClassCompileMeta;
use Orisai\ObjectMapper\Meta\Compile\CompileMeta;
use Orisai\ObjectMapper\Meta\Compile\FieldCompileMeta;
use Orisai\ObjectMapper\Meta\Compile\RuleCompileMeta;
use Orisai\ObjectMapper\Rules\MixedRule;
use Orisai\SourceMap\ClassSource;
use Orisai\SourceMap\FileSource;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionProperty;
use Tests\Orisai\ObjectMapper\Doubles\NoDefaultsVO;

final class CompileMetaTest extends TestCase
{

	public function test(): void
	{
		$reflector = new ReflectionClass(NoDefaultsVO::class);
		$classes = [
			new ClassCompileMeta([], [], [], $reflector),
		];
		$fields = [
			new FieldCompileMeta(
				[],
				[],
				[],
				new RuleCompileMeta(MixedRule::class, []),
				$reflector->getProperty('string'),
			),
		];
		$sources = [
			new ClassSource(new ReflectionClass(self::class)),
			new FileSource(__FILE__),
		];

		$meta = new CompileMeta($classes, $fields, $sources);

		self::assertSame(
			$classes,
			$meta->getClasses(),
		);
		self::assertSame(
			$fields,
			$meta->getFields(),
		);
		self::assertSame(
			$sources,
			$meta->getSources(),
		);
		self::assertTrue($meta->hasAnyAttributes());
	}

	public function testHasAnyAttributes(): void
	{
		$reflector = new ReflectionClass(NoDefaultsVO::class);

		$meta = new CompileMeta(
			[
				new ClassCompileMeta([], [], [], $reflector),
			],
			[],
			[],
		);
		self::assertFalse($meta->hasAnyAttributes());

		$meta = new CompileMeta(
			[
				new ClassCompileMeta(
					[
						new CallbackCompileMeta(BeforeCallback::class, []),
					],
					[],
					[],
					$reflector,
				),
			],
			[],
			[],
		);
		self::assertTrue($meta->hasAnyAttributes());

		$meta = new CompileMeta(
			[
				new ClassCompileMeta([], [], [], $reflector),
			],
			[
				new FieldCompileMeta(
					[],
					[],
					[],
					new RuleCompileMeta(MixedRule::class, []),
					new ReflectionProperty(NoDefaultsVO::class, 'string'),
				),
			],
			[],
		);
		self::assertTrue($meta->hasAnyAttributes());
	}

}
