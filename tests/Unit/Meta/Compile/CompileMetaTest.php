<?php declare(strict_types = 1);

namespace Tests\Orisai\ObjectMapper\Unit\Meta\Compile;

use Orisai\ObjectMapper\Callbacks\BeforeValidationCallback;
use Orisai\ObjectMapper\Meta\Compile\CallbackCompileMeta;
use Orisai\ObjectMapper\Meta\Compile\ClassCompileMeta;
use Orisai\ObjectMapper\Meta\Compile\CompileMeta;
use Orisai\ObjectMapper\Meta\Compile\FieldCompileMeta;
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
			[
				new FieldCompileMeta(
					[],
					[],
					[],
					[],
					new PropertyStructure(
						$reflector->getProperty('string'),
						new PropertySource($reflector->getProperty('string')),
						[],
					),
				),
			],
		];
		$sources = [
			new ClassSource(new ReflectionClass(self::class)),
			new FileSource(__FILE__),
		];
		$sourceName = 'test';
		$anyOfKey = 'any-of';
		$allOfKey = 'all-of';

		$meta = new CompileMeta($classes, $fields, $sources, $sourceName, $anyOfKey, $allOfKey);

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
		self::assertSame(
			$sourceName,
			$meta->getSourceName(),
		);
		self::assertSame(
			$anyOfKey,
			$meta->getAnyOfSourceKey(),
		);
		self::assertSame(
			$allOfKey,
			$meta->getAllOfSourceKey(),
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
			'test',
			'any-of',
			'all-of',
		);
		self::assertFalse($meta->hasAnyMeta());

		$meta = new CompileMeta(
			[
				new ClassCompileMeta(
					[
						new CallbackCompileMeta(BeforeValidationCallback::class, []),
					],
					[],
					[],
					$class,
				),
			],
			[],
			[],
			'test',
			'any-of',
			'all-of',
		);
		self::assertTrue($meta->hasAnyMeta());

		$meta = new CompileMeta(
			[
				new ClassCompileMeta([], [], [], $class),
			],
			[
				[
					new FieldCompileMeta(
						[],
						[],
						[],
						[],
						new PropertyStructure(
							$reflector->getProperty('string'),
							new PropertySource($reflector->getProperty('string')),
							[],
						),
					),
				],
			],
			[],
			'test',
			'any-of',
			'all-of',
		);
		self::assertTrue($meta->hasAnyMeta());
	}

}
