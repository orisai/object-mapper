<?php declare(strict_types = 1);

namespace Tests\Orisai\ObjectMapper\Unit\Meta\Runtime;

use Orisai\ObjectMapper\Callbacks\BaseCallbackArgs;
use Orisai\ObjectMapper\Callbacks\BeforeCallback;
use Orisai\ObjectMapper\Docs\DescriptionDoc;
use Orisai\ObjectMapper\Meta\DocMeta;
use Orisai\ObjectMapper\Meta\Runtime\CallbackRuntimeMeta;
use Orisai\ObjectMapper\Meta\Runtime\ClassRuntimeMeta;
use Orisai\ObjectMapper\Meta\Runtime\ModifierRuntimeMeta;
use Orisai\ObjectMapper\Modifiers\FieldNameArgs;
use Orisai\ObjectMapper\Modifiers\FieldNameModifier;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

final class ClassRuntimeMetaTest extends TestCase
{

	public function test(): void
	{
		$callbacks = [
			new CallbackRuntimeMeta(
				BeforeCallback::class,
				new BaseCallbackArgs('method', false, false),
				new ReflectionClass(self::class),
			),
		];
		$docs = [
			new DocMeta(DescriptionDoc::class, []),
		];
		$modifiers = [
			new ModifierRuntimeMeta(FieldNameModifier::class, new FieldNameArgs('field')),
		];

		$meta = new ClassRuntimeMeta($callbacks, $docs, $modifiers);

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
	}

}
