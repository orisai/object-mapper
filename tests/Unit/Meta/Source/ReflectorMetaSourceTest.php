<?php declare(strict_types = 1);

namespace Tests\Orisai\ObjectMapper\Unit\Meta\Source;

use Generator;
use Orisai\Exceptions\Logic\InvalidArgument;
use Orisai\ObjectMapper\MappedObject;
use Orisai\ObjectMapper\Meta\Source\AnnotationsMetaSource;
use Orisai\ObjectMapper\Meta\Source\ReflectorMetaSource;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use Tests\Orisai\ObjectMapper\Doubles\Meta\FieldWithMultipleRulesChildVO;
use Tests\Orisai\ObjectMapper\Doubles\Meta\FieldWithMultipleRulesVO;
use Tests\Orisai\ObjectMapper\Doubles\Meta\FieldWithNoRuleChildVO;
use Tests\Orisai\ObjectMapper\Doubles\Meta\FieldWithNoRuleVO;
use Tests\Orisai\ObjectMapper\Doubles\Meta\RuleAboveClassChildVO;
use Tests\Orisai\ObjectMapper\Doubles\Meta\RuleAboveClassVO;
use Tests\Orisai\ObjectMapper\Doubles\Meta\UnsupportedClassDefinitionVO;
use Tests\Orisai\ObjectMapper\Doubles\Meta\UnsupportedPropertyDefinitionVO;
use Tests\Orisai\ObjectMapper\Doubles\Meta\VariantFieldChildVO;
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

	public function testFieldInvarianceRelativeName(): void
	{
		$this->expectException(InvalidArgument::class);

		$this->expectExceptionMessage(
			<<<'MSG'
Context: Resolving metadata of mapped object
         'Tests\Orisai\ObjectMapper\Doubles\Meta\VariantFieldVO'.
Problem: Definition in annotation of property '$field' differs from definition
         in annotation of property
         'Tests\Orisai\ObjectMapper\Doubles\Meta\VariantFieldParentVO->$field'.
Solution: Don't override metadata of properties in child classes.
MSG,
		);

		$this->source->load(new ReflectionClass(VariantFieldVO::class));
	}

	public function testFieldInvarianceFullName(): void
	{
		$this->expectException(InvalidArgument::class);
		$this->expectExceptionMessage(
			<<<'MSG'
Context: Resolving metadata of mapped object
         'Tests\Orisai\ObjectMapper\Doubles\Meta\VariantFieldChildVO'.
Problem: Definition in annotation of property
         'Tests\Orisai\ObjectMapper\Doubles\Meta\VariantFieldVO->$field' differs
         from definition in annotation of property
         'Tests\Orisai\ObjectMapper\Doubles\Meta\VariantFieldParentVO->$field'.
Solution: Don't override metadata of properties in child classes.
MSG,
		);

		$this->source->load(new ReflectionClass(VariantFieldChildVO::class));
	}

	public function testRuleAboveClassRelativeName(): void
	{
		$this->expectException(InvalidArgument::class);
		$this->expectExceptionMessage(
			<<<'MSG'
Context: Resolving metadata of mapped object
         'Tests\Orisai\ObjectMapper\Doubles\Meta\RuleAboveClassVO'.
Problem: Rule definition
         'Tests\Orisai\ObjectMapper\Doubles\Definition\TargetLessRuleDefinition'
         cannot be used on class, it is only allowed on properties.
MSG,
		);

		$this->source->load(new ReflectionClass(RuleAboveClassVO::class));
	}

	public function testRuleAboveClassFullName(): void
	{
		$this->expectException(InvalidArgument::class);
		$this->expectExceptionMessage(
			<<<'MSG'
Context: Resolving metadata of mapped object
         'Tests\Orisai\ObjectMapper\Doubles\Meta\RuleAboveClassChildVO'.
Problem: Rule definition
         'Tests\Orisai\ObjectMapper\Doubles\Definition\TargetLessRuleDefinition'
         (used above class
         'Tests\Orisai\ObjectMapper\Doubles\Meta\RuleAboveClassVO') cannot be
         used on class, it is only allowed on properties.
MSG,
		);

		$this->source->load(new ReflectionClass(RuleAboveClassChildVO::class));
	}

	public function testFieldWithMultipleRulesRelativeName(): void
	{
		$this->expectException(InvalidArgument::class);
		$this->expectExceptionMessage(
			<<<'MSG'
Context: Resolving metadata of mapped object
         'Tests\Orisai\ObjectMapper\Doubles\Meta\FieldWithMultipleRulesVO'.
Problem: Property '$field' has multiple rule definitions (in annotation), but
         only one is allowed.
Solution: Combine multiple with 'Orisai\ObjectMapper\Rules\AnyOf' or
          'Orisai\ObjectMapper\Rules\AllOf'.
MSG,
		);

		$this->source->load(new ReflectionClass(FieldWithMultipleRulesVO::class));
	}

	public function testFieldWithMultipleRulesAbsoluteName(): void
	{
		$this->expectException(InvalidArgument::class);
		$this->expectExceptionMessage(
			<<<'MSG'
Context: Resolving metadata of mapped object
         'Tests\Orisai\ObjectMapper\Doubles\Meta\FieldWithMultipleRulesChildVO'.
Problem: Property
         'Tests\Orisai\ObjectMapper\Doubles\Meta\FieldWithMultipleRulesVO->$field'
         has multiple rule definitions (in annotation), but only one is allowed.
Solution: Combine multiple with 'Orisai\ObjectMapper\Rules\AnyOf' or
          'Orisai\ObjectMapper\Rules\AllOf'.
MSG,
		);

		$this->source->load(new ReflectionClass(FieldWithMultipleRulesChildVO::class));
	}

	public function testFieldWithNoRuleRelativeName(): void
	{
		$this->expectException(InvalidArgument::class);
		$this->expectExceptionMessage(
			<<<'MSG'
Context: Resolving metadata of mapped object
         'Tests\Orisai\ObjectMapper\Doubles\Meta\FieldWithNoRuleVO'.
Problem: Property '$field' has some mapped object definition (in annotation),
         but no rule definition.
Solution: Either remove the definition or add a rule definition.
MSG,
		);

		$this->source->load(new ReflectionClass(FieldWithNoRuleVO::class));
	}

	public function testFieldWithNoRuleAbsoluteName(): void
	{
		$this->expectException(InvalidArgument::class);
		$this->expectExceptionMessage(
			<<<'MSG'
Context: Resolving metadata of mapped object
         'Tests\Orisai\ObjectMapper\Doubles\Meta\FieldWithNoRuleChildVO'.
Problem: Property
         'Tests\Orisai\ObjectMapper\Doubles\Meta\FieldWithNoRuleVO->$field' has
         some mapped object definition (in annotation), but no rule definition.
Solution: Either remove the definition or add a rule definition.
MSG,
		);

		$this->source->load(new ReflectionClass(FieldWithNoRuleChildVO::class));
	}

}
