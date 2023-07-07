<?php declare(strict_types = 1);

namespace Tests\Orisai\ObjectMapper\Unit\Meta\Compile;

use Orisai\ObjectMapper\Callbacks\BeforeCallback;
use Orisai\ObjectMapper\Meta\Compile\CallbackCompileMeta;
use Orisai\ObjectMapper\Meta\Compile\ClassCompileMeta;
use Orisai\ObjectMapper\Meta\Compile\CompileMeta;
use Orisai\ObjectMapper\Meta\Compile\FieldCompileMeta;
use Orisai\ObjectMapper\Meta\Compile\RuleCompileMeta;
use Orisai\ObjectMapper\Rules\MixedRule;
use Orisai\ReflectionMeta\Structure\ClassStructure;
use Orisai\ReflectionMeta\Structure\PropertyStructure;
use Orisai\SourceMap\ClassSource;
use Orisai\SourceMap\FileSource;
use Orisai\SourceMap\PropertySource;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use Tests\Orisai\ObjectMapper\Doubles\NoDefaultsVO;

final class CompileMetaTest extends TestCase
{

	public function test(): void
	{
		$reflector = new ReflectionClass(NoDefaultsVO::class);
		$class = new ClassStructure(
			$reflector,
			new ClassSource($reflector),
		);
		$classes = [
			new ClassCompileMeta([], [], [], $class),
		];
		$fields = [
			new FieldCompileMeta(
				[],
				[],
				[],
				new RuleCompileMeta(MixedRule::class, []),
				new PropertyStructure(
					$reflector,
					new PropertySource($reflector->getProperty('string')),
					[],
				),
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
		self::assertTrue($meta->hasAnyMeta());
	}

	public function testHasAnyAttributes(): void
	{
		$reflector = new ReflectionClass(NoDefaultsVO::class);
		$class = new ClassStructure(
			$reflector,
			new ClassSource($reflector),
		);

		$meta = new CompileMeta(
			[
				new ClassCompileMeta([], [], [], $class),
			],
			[],
			[],
		);
		self::assertFalse($meta->hasAnyMeta());

		$meta = new CompileMeta(
			[
				new ClassCompileMeta(
					[
						new CallbackCompileMeta(BeforeCallback::class, []),
					],
					[],
					[],
					$class,
				),
			],
			[],
			[],
		);
		self::assertTrue($meta->hasAnyMeta());

		$meta = new CompileMeta(
			[
				new ClassCompileMeta([], [], [], $class),
			],
			[
				new FieldCompileMeta(
					[],
					[],
					[],
					new RuleCompileMeta(MixedRule::class, []),
					new PropertyStructure(
						$reflector,
						new PropertySource($reflector->getProperty('string')),
						[],
					),
				),
			],
			[],
		);
		self::assertTrue($meta->hasAnyMeta());
	}

}
