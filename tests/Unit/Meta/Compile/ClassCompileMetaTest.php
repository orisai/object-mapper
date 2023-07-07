<?php declare(strict_types = 1);

namespace Tests\Orisai\ObjectMapper\Unit\Meta\Compile;

use Orisai\ObjectMapper\Callbacks\BeforeCallback;
use Orisai\ObjectMapper\Docs\DescriptionDoc;
use Orisai\ObjectMapper\Meta\Compile\CallbackCompileMeta;
use Orisai\ObjectMapper\Meta\Compile\ClassCompileMeta;
use Orisai\ObjectMapper\Meta\Compile\ModifierCompileMeta;
use Orisai\ObjectMapper\Meta\Shared\DocMeta;
use Orisai\ObjectMapper\Modifiers\FieldNameModifier;
use Orisai\ReflectionMeta\Structure\ClassStructure;
use Orisai\SourceMap\ClassSource;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use Tests\Orisai\ObjectMapper\Doubles\NoDefaultsVO;

final class ClassCompileMetaTest extends TestCase
{

	public function test(): void
	{
		$callbacks = [
			new CallbackCompileMeta(BeforeCallback::class, []),
		];
		$docs = [
			new DocMeta(DescriptionDoc::class, []),
		];
		$modifiers = [
			new ModifierCompileMeta(FieldNameModifier::class, []),
		];
		$reflector = new ReflectionClass(NoDefaultsVO::class);
		$class = new ClassStructure(
			$reflector,
			new ClassSource($reflector),
		);

		$meta = new ClassCompileMeta($callbacks, $docs, $modifiers, $class);

		self::assertSame(
			$callbacks,
			$meta->getCallbacks(),
		);
		self::assertSame(
			$docs,
			$meta->getDocs(),
		);
		self::assertSame(
			$modifiers,
			$meta->getModifiers(),
		);
		self::assertSame(
			$class,
			$meta->getClass(),
		);
		self::assertTrue($meta->hasAnyMeta());

		$meta = new ClassCompileMeta($callbacks, [], [], $class);
		self::assertTrue($meta->hasAnyMeta());

		$meta = new ClassCompileMeta([], $docs, [], $class);
		self::assertTrue($meta->hasAnyMeta());

		$meta = new ClassCompileMeta([], [], $modifiers, $class);
		self::assertTrue($meta->hasAnyMeta());

		$meta = new ClassCompileMeta([], [], [], $class);
		self::assertFalse($meta->hasAnyMeta());
	}

}
