<?php declare(strict_types = 1);

namespace Tests\Orisai\ObjectMapper\Unit\Meta\Source;

use Generator;
use Orisai\Exceptions\Logic\InvalidArgument;
use Orisai\ObjectMapper\MappedObject;
use Orisai\ObjectMapper\Meta\Source\AnnotationsMetaSource;
use Orisai\ObjectMapper\Meta\Source\ReflectorMetaSource;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use Tests\Orisai\ObjectMapper\Doubles\Meta\FieldWithMultipleRulesVO;
use Tests\Orisai\ObjectMapper\Doubles\Meta\FieldWithNoRuleVO;
use Tests\Orisai\ObjectMapper\Doubles\Meta\RuleAboveClassVO;
use Tests\Orisai\ObjectMapper\Doubles\Meta\UnsupportedClassDefinitionVO;
use Tests\Orisai\ObjectMapper\Doubles\Meta\UnsupportedPropertyDefinitionVO;
use Tests\Orisai\ObjectMapper\Doubles\Meta\VariantFieldVO;

final class ReflectorMetaSourceTest extends TestCase
{

	private ReflectorMetaSource $source;

	protected function setUp(): void
	{
		$this->source = new AnnotationsMetaSource();
	}

	/**
	 * @param class-string<MappedObject> $class
	 *
	 * @dataProvider provideUnsupportedDefinitionType
	 */
	public function testUnsupportedDefinitionType(string $class): void
	{
		$this->expectException(InvalidArgument::class);
		$this->expectExceptionMessage(
			"Definition 'Tests\Orisai\ObjectMapper\Doubles\Definition\UnsupportedDefinition' "
			. "(subtype of 'Orisai\ObjectMapper\Meta\MetaDefinition') should implement "
			. "'Orisai\ObjectMapper\Callbacks\CallbackDefinition', "
			. "'Orisai\ObjectMapper\Docs\DocDefinition', "
			. "'Orisai\ObjectMapper\Modifiers\ModifierDefinition' or "
			. "'Orisai\ObjectMapper\Rules\RuleDefinition'.",
		);

		$this->source->load(new ReflectionClass($class));
	}

	public function provideUnsupportedDefinitionType(): Generator
	{
		yield [
			UnsupportedClassDefinitionVO::class,
		];

		yield [
			UnsupportedPropertyDefinitionVO::class,
		];
	}

	public function testFieldInvariance(): void
	{
		$this->expectException(InvalidArgument::class);
		$this->expectExceptionMessage(
			<<<'MSG'
Context: Resolving metadata of mapped object
         'Tests\Orisai\ObjectMapper\Doubles\Meta\VariantFieldVO'.
Problem: Definition of property
         'Tests\Orisai\ObjectMapper\Doubles\Meta\VariantFieldVO->$field' can't
         be changed but it differs from definition
         'Tests\Orisai\ObjectMapper\Doubles\Meta\VariantFieldParentVO->$field'.
MSG,
		);

		$this->source->load(new ReflectionClass(VariantFieldVO::class));
	}

	public function testRuleAboveClass(): void
	{
		$this->expectException(InvalidArgument::class);
		$this->expectExceptionMessage(
			<<<'MSG'
Context: Resolving metadata of mapped object
         'Tests\Orisai\ObjectMapper\Doubles\Meta\RuleAboveClassVO'.
Problem: Rule definition
         'Tests\Orisai\ObjectMapper\Doubles\Definition\TargetLessRuleDefinition'
         (subtype of 'Orisai\ObjectMapper\Rules\RuleDefinition') cannot be used
         on class, only properties are allowed.
MSG,
		);

		$this->source->load(new ReflectionClass(RuleAboveClassVO::class));
	}

	public function testFieldWithMultipleRules(): void
	{
		$this->expectException(InvalidArgument::class);
		$this->expectExceptionMessage(
			<<<'MSG'
Context: Resolving metadata of mapped object
         'Tests\Orisai\ObjectMapper\Doubles\Meta\FieldWithMultipleRulesVO'.
Problem: Property
         'Tests\Orisai\ObjectMapper\Doubles\Meta\FieldWithMultipleRulesVO->$field'
         has multiple rule definitions, but only one is allowed.
Solution: Combine multiple with 'Orisai\ObjectMapper\Rules\AnyOf' or
          'Orisai\ObjectMapper\Rules\AllOf'.
MSG,
		);

		$this->source->load(new ReflectionClass(FieldWithMultipleRulesVO::class));
	}

	public function testFieldWithNoRule(): void
	{
		$this->expectException(InvalidArgument::class);
		$this->expectExceptionMessage(
			<<<'MSG'
Context: Resolving metadata of mapped object
         'Tests\Orisai\ObjectMapper\Doubles\Meta\FieldWithNoRuleVO'.
Problem: Property
         'Tests\Orisai\ObjectMapper\Doubles\Meta\FieldWithNoRuleVO->$field' has
         mapped object definition, but no rule definition.
MSG,
		);

		$this->source->load(new ReflectionClass(FieldWithNoRuleVO::class));
	}

}
