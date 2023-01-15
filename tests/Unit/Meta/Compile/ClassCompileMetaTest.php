<?php declare(strict_types = 1);

namespace Tests\Orisai\ObjectMapper\Unit\Meta\Compile;

use Orisai\ObjectMapper\Callbacks\BeforeCallback;
use Orisai\ObjectMapper\Docs\DescriptionDoc;
use Orisai\ObjectMapper\Meta\Compile\CallbackCompileMeta;
use Orisai\ObjectMapper\Meta\Compile\ClassCompileMeta;
use Orisai\ObjectMapper\Meta\Compile\ModifierCompileMeta;
use Orisai\ObjectMapper\Meta\DocMeta;
use Orisai\ObjectMapper\Modifiers\FieldNameModifier;
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

		$meta = new ClassCompileMeta($callbacks, $docs, $modifiers, $reflector);

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
			$reflector,
			$meta->getClass(),
		);
		self::assertTrue($meta->hasAnyAttributes());

		$meta = new ClassCompileMeta($callbacks, [], [], $reflector);
		self::assertTrue($meta->hasAnyAttributes());

		$meta = new ClassCompileMeta([], $docs, [], $reflector);
		self::assertTrue($meta->hasAnyAttributes());

		$meta = new ClassCompileMeta([], [], $modifiers, $reflector);
		self::assertTrue($meta->hasAnyAttributes());

		$meta = new ClassCompileMeta([], [], [], $reflector);
		self::assertFalse($meta->hasAnyAttributes());
	}

}
