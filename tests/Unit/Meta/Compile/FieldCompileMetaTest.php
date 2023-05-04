<?php declare(strict_types = 1);

namespace Tests\Orisai\ObjectMapper\Unit\Meta\Compile;

use Orisai\ObjectMapper\Callbacks\BeforeCallback;
use Orisai\ObjectMapper\Docs\DescriptionDoc;
use Orisai\ObjectMapper\Meta\Compile\CallbackCompileMeta;
use Orisai\ObjectMapper\Meta\Compile\FieldCompileMeta;
use Orisai\ObjectMapper\Meta\Compile\ModifierCompileMeta;
use Orisai\ObjectMapper\Meta\Compile\RuleCompileMeta;
use Orisai\ObjectMapper\Meta\Shared\DocMeta;
use Orisai\ObjectMapper\Modifiers\FieldNameModifier;
use Orisai\ObjectMapper\Rules\MixedRule;
use PHPUnit\Framework\TestCase;
use ReflectionProperty;
use Tests\Orisai\ObjectMapper\Doubles\NoDefaultsVO;

final class FieldCompileMetaTest extends TestCase
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
		$rule = new RuleCompileMeta(MixedRule::class, []);
		$property = new ReflectionProperty(NoDefaultsVO::class, 'string');

		$meta = new FieldCompileMeta($callbacks, $docs, $modifiers, $rule, $property);

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
			$rule,
			$meta->getRule(),
		);
		self::assertSame(
			$property,
			$meta->getProperty(),
		);
	}

}
