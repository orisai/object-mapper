<?php declare(strict_types = 1);

namespace Tests\Orisai\ObjectMapper\Unit\Meta\Runtime;

use Orisai\ObjectMapper\Args\EmptyArgs;
use Orisai\ObjectMapper\Callbacks\AfterCallback;
use Orisai\ObjectMapper\Callbacks\BaseCallbackArgs;
use Orisai\ObjectMapper\Callbacks\BeforeCallback;
use Orisai\ObjectMapper\Callbacks\CallbackRuntime;
use Orisai\ObjectMapper\Docs\DescriptionDoc;
use Orisai\ObjectMapper\Meta\Runtime\CallbackRuntimeMeta;
use Orisai\ObjectMapper\Meta\Runtime\FieldRuntimeMeta;
use Orisai\ObjectMapper\Meta\Runtime\ModifierRuntimeMeta;
use Orisai\ObjectMapper\Meta\Runtime\RuleRuntimeMeta;
use Orisai\ObjectMapper\Meta\Shared\DefaultValueMeta;
use Orisai\ObjectMapper\Meta\Shared\DocMeta;
use Orisai\ObjectMapper\Modifiers\FieldNameArgs;
use Orisai\ObjectMapper\Modifiers\FieldNameModifier;
use Orisai\ObjectMapper\Rules\MixedRule;
use PHPUnit\Framework\TestCase;
use ReflectionProperty;
use Tests\Orisai\ObjectMapper\Doubles\NoDefaultsVO;
use function serialize;
use function unserialize;

final class FieldRuntimeMetaTest extends TestCase
{

	public function test(): void
	{
		$property = new ReflectionProperty(NoDefaultsVO::class, 'string');

		$beforeCallbacks = [
			new CallbackRuntimeMeta(
				BeforeCallback::class,
				new BaseCallbackArgs('method', false, false, CallbackRuntime::process()),
				$property->getDeclaringClass(),
			),
		];

		$callbacks = [
			BeforeCallback::class => $beforeCallbacks,
		];
		$docs = [
			DescriptionDoc::getUniqueName() => new DocMeta(DescriptionDoc::class, []),
		];
		$modifiers = [
			FieldNameModifier::class => new ModifierRuntimeMeta(FieldNameModifier::class, new FieldNameArgs('field')),
		];
		$rule = new RuleRuntimeMeta(MixedRule::class, new EmptyArgs());
		$default = DefaultValueMeta::fromNothing();

		$meta = new FieldRuntimeMeta($callbacks, $docs, $modifiers, $rule, $default, $property);

		self::assertSame($callbacks, $meta->callbacks);
		self::assertSame(
			$beforeCallbacks,
			$meta->getCallbacksByType(BeforeCallback::class),
		);
		self::assertSame(
			[],
			$meta->getCallbacksByType(AfterCallback::class),
		);
		self::assertSame($docs, $meta->docs);
		self::assertSame($modifiers, $meta->modifiers);
		self::assertSame($rule, $meta->rule);
		self::assertSame($property, $meta->property);
		self::assertEquals($meta, unserialize(serialize($meta)));
	}

}
