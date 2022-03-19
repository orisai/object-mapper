<?php declare(strict_types = 1);

namespace Tests\Orisai\ObjectMapper\Unit\Meta\Compile;

use Orisai\ObjectMapper\Callbacks\BeforeCallback;
use Orisai\ObjectMapper\Docs\DescriptionDoc;
use Orisai\ObjectMapper\Meta\CallbackMeta;
use Orisai\ObjectMapper\Meta\Compile\PropertyCompileMeta;
use Orisai\ObjectMapper\Meta\DocMeta;
use Orisai\ObjectMapper\Meta\ModifierMeta;
use Orisai\ObjectMapper\Meta\RuleMeta;
use Orisai\ObjectMapper\Modifiers\FieldNameModifier;
use Orisai\ObjectMapper\Rules\MixedRule;
use PHPUnit\Framework\TestCase;

final class PropertyCompileMetaTest extends TestCase
{

	public function test(): void
	{
		$callbacks = [
			new CallbackMeta(BeforeCallback::class, []),
		];
		$docs = [
			new DocMeta(DescriptionDoc::class, []),
		];
		$modifiers = [
			new ModifierMeta(FieldNameModifier::class, []),
		];
		$rule = new RuleMeta(MixedRule::class, []);

		$meta = new PropertyCompileMeta($callbacks, $docs, $modifiers, $rule);

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
	}

}
