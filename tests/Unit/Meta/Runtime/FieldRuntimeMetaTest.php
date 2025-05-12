<?php declare(strict_types = 1);

namespace Tests\Orisai\ObjectMapper\Unit\Meta\Runtime;

use Orisai\ObjectMapper\Args\EmptyArgs;
use Orisai\ObjectMapper\Callbacks\AfterValidationCallback;
use Orisai\ObjectMapper\Callbacks\BeforeValidationCallback;
use Orisai\ObjectMapper\Callbacks\CallbackRuntime;
use Orisai\ObjectMapper\Callbacks\ValidationCallbackArgs;
use Orisai\ObjectMapper\Meta\Runtime\CallbackRuntimeMeta;
use Orisai\ObjectMapper\Meta\Runtime\FieldRuntimeMeta;
use Orisai\ObjectMapper\Meta\Runtime\PhpMethodMeta;
use Orisai\ObjectMapper\Meta\Runtime\PhpPropertyMeta;
use Orisai\ObjectMapper\Meta\Runtime\RuleRuntimeMeta;
use Orisai\ObjectMapper\Meta\Shared\DefaultValueMeta;
use Orisai\ObjectMapper\Rules\MixedRule;
use PHPUnit\Framework\TestCase;
use Tests\Orisai\ObjectMapper\Doubles\NoDefaultsVO;
use function serialize;
use function unserialize;

final class FieldRuntimeMetaTest extends TestCase
{

	public function test(): void
	{
		$property = new PhpPropertyMeta(NoDefaultsVO::class, 'property', true);

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
		$afterCallbacks = [
			new CallbackRuntimeMeta(
				AfterValidationCallback::class,
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
		$rule = new RuleRuntimeMeta(MixedRule::class, new EmptyArgs());
		$default = DefaultValueMeta::fromNothing();

		$meta = new FieldRuntimeMeta($beforeCallbacks, $afterCallbacks, $rule, $default, $property);

		self::assertSame(
			$beforeCallbacks,
			$meta->getBeforeValidationCallbacks(),
		);
		self::assertSame(
			$afterCallbacks,
			$meta->getAfterValidationCallbacks(),
		);
		self::assertSame($rule, $meta->rule);
		self::assertSame($property, $meta->property);
		self::assertEquals($meta, unserialize(serialize($meta)));
	}

}
