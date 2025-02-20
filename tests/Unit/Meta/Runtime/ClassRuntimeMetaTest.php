<?php declare(strict_types = 1);

namespace Tests\Orisai\ObjectMapper\Unit\Meta\Runtime;

use Orisai\ObjectMapper\Callbacks\AfterValidationCallback;
use Orisai\ObjectMapper\Callbacks\BeforeValidationCallback;
use Orisai\ObjectMapper\Callbacks\CallbackRuntime;
use Orisai\ObjectMapper\Callbacks\ValidationCallbackArgs;
use Orisai\ObjectMapper\Docs\DescriptionDoc;
use Orisai\ObjectMapper\Meta\Runtime\CallbackRuntimeMeta;
use Orisai\ObjectMapper\Meta\Runtime\ClassRuntimeMeta;
use Orisai\ObjectMapper\Meta\Runtime\ModifierRuntimeMeta;
use Orisai\ObjectMapper\Meta\Shared\DocMeta;
use Orisai\ObjectMapper\Modifiers\RequiresDependenciesArgs;
use Orisai\ObjectMapper\Modifiers\RequiresDependenciesModifier;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use Tests\Orisai\ObjectMapper\Doubles\DefaultsVO;
use Tests\Orisai\ObjectMapper\Doubles\Dependencies\DependenciesUsingVoInjector;
use function serialize;
use function unserialize;

final class ClassRuntimeMetaTest extends TestCase
{

	public function test(): void
	{
		$beforeCallbacks = [
			new CallbackRuntimeMeta(
				BeforeValidationCallback::class,
				new ValidationCallbackArgs('method', false, false, CallbackRuntime::process()),
				new ReflectionClass(DefaultsVO::class),
			),
		];
		$callbacks = [
			BeforeValidationCallback::class => $beforeCallbacks,
		];
		$docs = [
			DescriptionDoc::getUniqueName() => new DocMeta(DescriptionDoc::class, []),
		];
		$modifiers = [
			RequiresDependenciesModifier::class => [
				new ModifierRuntimeMeta(
					RequiresDependenciesModifier::class,
					new RequiresDependenciesArgs(DependenciesUsingVoInjector::class),
				),
			],
		];

		$meta = new ClassRuntimeMeta($callbacks, $docs, $modifiers);

		self::assertSame($callbacks, $meta->callbacks);
		self::assertSame(
			$beforeCallbacks,
			$meta->getCallbacksByType(BeforeValidationCallback::class),
		);
		self::assertSame(
			[],
			$meta->getCallbacksByType(AfterValidationCallback::class),
		);
		self::assertSame($docs, $meta->docs);
		self::assertSame($modifiers, $meta->modifiers);
		self::assertEquals($meta, unserialize(serialize($meta)));
	}

}
