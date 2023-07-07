<?php declare(strict_types = 1);

namespace Tests\Orisai\ObjectMapper\Unit\Meta\Compile;

use Orisai\ObjectMapper\Callbacks\AfterCallback;
use Orisai\ObjectMapper\Callbacks\BeforeCallback;
use Orisai\ObjectMapper\Docs\DescriptionDoc;
use Orisai\ObjectMapper\Docs\SummaryDoc;
use Orisai\ObjectMapper\Meta\Compile\CallbackCompileMeta;
use Orisai\ObjectMapper\Meta\Compile\FieldCompileMeta;
use Orisai\ObjectMapper\Meta\Compile\ModifierCompileMeta;
use Orisai\ObjectMapper\Meta\Compile\RuleCompileMeta;
use Orisai\ObjectMapper\Meta\Shared\DocMeta;
use Orisai\ObjectMapper\Modifiers\DefaultValueModifier;
use Orisai\ObjectMapper\Modifiers\FieldNameModifier;
use Orisai\ObjectMapper\Rules\MixedRule;
use Orisai\ObjectMapper\Rules\StringRule;
use Orisai\ReflectionMeta\Structure\PropertyStructure;
use Orisai\SourceMap\PropertySource;
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
		$reflector = new ReflectionProperty(NoDefaultsVO::class, 'string');
		$property = new PropertyStructure(
			$reflector,
			new PropertySource($reflector),
			[],
		);

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
		self::assertTrue($meta->hasEqualMeta($meta));
	}

	public function testUnequalRuleMeta(): void
	{
		$reflector = new ReflectionProperty(NoDefaultsVO::class, 'string');
		$property = new PropertyStructure(
			$reflector,
			new PropertySource($reflector),
			[],
		);

		$meta1 = new FieldCompileMeta(
			[],
			[],
			[],
			new RuleCompileMeta(MixedRule::class, []),
			$property,
		);
		$meta2 = new FieldCompileMeta(
			[],
			[],
			[],
			new RuleCompileMeta(StringRule::class, []),
			$property,
		);

		self::assertFalse($meta1->hasEqualMeta($meta2));
	}

	public function testUnequalCallbacksMeta(): void
	{
		$reflector = new ReflectionProperty(NoDefaultsVO::class, 'string');
		$property = new PropertyStructure(
			$reflector,
			new PropertySource($reflector),
			[],
		);

		$meta1 = new FieldCompileMeta(
			[
				new CallbackCompileMeta(BeforeCallback::class, []),
			],
			[],
			[],
			new RuleCompileMeta(MixedRule::class, []),
			$property,
		);
		$meta2 = new FieldCompileMeta(
			[
				new CallbackCompileMeta(AfterCallback::class, []),
			],
			[],
			[],
			new RuleCompileMeta(MixedRule::class, []),
			$property,
		);

		self::assertFalse($meta1->hasEqualMeta($meta2));
	}

	public function testUnequalDocsMeta(): void
	{
		$reflector = new ReflectionProperty(NoDefaultsVO::class, 'string');
		$property = new PropertyStructure(
			$reflector,
			new PropertySource($reflector),
			[],
		);

		$meta1 = new FieldCompileMeta(
			[],
			[
				new DocMeta(SummaryDoc::class, []),
			],
			[],
			new RuleCompileMeta(MixedRule::class, []),
			$property,
		);
		$meta2 = new FieldCompileMeta(
			[],
			[
				new DocMeta(DescriptionDoc::class, []),
			],
			[],
			new RuleCompileMeta(MixedRule::class, []),
			$property,
		);

		self::assertFalse($meta1->hasEqualMeta($meta2));
	}

	public function testUnequalModifiersMeta(): void
	{
		$reflector = new ReflectionProperty(NoDefaultsVO::class, 'string');
		$property = new PropertyStructure(
			$reflector,
			new PropertySource($reflector),
			[],
		);

		$meta1 = new FieldCompileMeta(
			[],
			[],
			[
				new ModifierCompileMeta(DefaultValueModifier::class, []),
			],
			new RuleCompileMeta(MixedRule::class, []),
			$property,
		);
		$meta2 = new FieldCompileMeta(
			[],
			[],
			[
				new ModifierCompileMeta(FieldNameModifier::class, []),
			],
			new RuleCompileMeta(MixedRule::class, []),
			$property,
		);

		self::assertFalse($meta1->hasEqualMeta($meta2));
	}

}
