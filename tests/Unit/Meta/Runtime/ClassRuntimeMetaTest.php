<?php declare(strict_types = 1);

namespace Tests\Orisai\ObjectMapper\Unit\Meta\Runtime;

use Orisai\ObjectMapper\Callbacks\BeforeValidationCallback;
use Orisai\ObjectMapper\Callbacks\CallbackRuntime;
use Orisai\ObjectMapper\Callbacks\ValidationCallbackArgs;
use Orisai\ObjectMapper\Meta\Runtime\CallbackRuntimeMeta;
use Orisai\ObjectMapper\Meta\Runtime\ClassRuntimeMeta;
use Orisai\ObjectMapper\Meta\Runtime\ModifierRuntimeMeta;
use Orisai\ObjectMapper\Meta\Runtime\PhpMethodMeta;
use Orisai\ObjectMapper\Modifiers\RequiresDependenciesArgs;
use Orisai\ObjectMapper\Modifiers\RequiresDependenciesModifier;
use PHPUnit\Framework\TestCase;
use Tests\Orisai\ObjectMapper\Doubles\Dependencies\DependenciesUsingVoInjector;
use Tests\Orisai\ObjectMapper\Doubles\NoDefaultsVO;
use function serialize;
use function unserialize;

final class ClassRuntimeMetaTest extends TestCase
{

	public function test(): void
	{
		$beforeCallbacks = [
			new CallbackRuntimeMeta(
				BeforeValidationCallback::class,
				new ValidationCallbackArgs(
					CallbackRuntime::process(),
					new PhpMethodMeta(
						NoDefaultsVO::class,
						'method',
						false,
						false,
						false,
					),
				),
			),
		];
		$callbacks = [
			BeforeValidationCallback::class => $beforeCallbacks,
		];
		$modifiers = [
			RequiresDependenciesModifier::class => [
				new ModifierRuntimeMeta(
					RequiresDependenciesModifier::class,
					new RequiresDependenciesArgs(DependenciesUsingVoInjector::class),
				),
			],
		];

		$meta = new ClassRuntimeMeta($callbacks, $modifiers);

		self::assertSame($callbacks, $meta->callbacks);
		self::assertSame(
			$beforeCallbacks,
			$meta->getBeforeValidationCallbacks(),
		);
		self::assertSame(
			[],
			$meta->getAfterValidationCallbacks(),
		);
		self::assertSame(
			[],
			$meta->getAfterMappingCallbacks(),
		);
		self::assertSame($modifiers, $meta->modifiers);
		self::assertEquals($meta, unserialize(serialize($meta)));
	}

}
