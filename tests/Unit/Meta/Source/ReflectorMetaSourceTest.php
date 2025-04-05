<?php declare(strict_types = 1);

namespace Tests\Orisai\ObjectMapper\Unit\Meta\Source;

use Generator;
use Orisai\Exceptions\Logic\InvalidArgument;
use Orisai\ObjectMapper\MappedObject;
use Orisai\ObjectMapper\Meta\Source\AnnotationsMetaSource;
use Orisai\ObjectMapper\Meta\Source\ReflectorMetaSource;
use Orisai\ReflectionMeta\Structure\StructureBuilder;
use Orisai\ReflectionMeta\Structure\StructureFlattener;
use Orisai\ReflectionMeta\Structure\StructureGroup;
use Orisai\ReflectionMeta\Structure\StructureGrouper;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use Tests\Orisai\ObjectMapper\Doubles\Invalid\FieldWithMultipleRulesChildVO;
use Tests\Orisai\ObjectMapper\Doubles\Invalid\FieldWithMultipleRulesVO;
use Tests\Orisai\ObjectMapper\Doubles\Invalid\FieldWithNoRuleChildVO;
use Tests\Orisai\ObjectMapper\Doubles\Invalid\FieldWithNoRuleVO;
use Tests\Orisai\ObjectMapper\Doubles\Invalid\RuleAboveClassChildVO;
use Tests\Orisai\ObjectMapper\Doubles\Invalid\RuleAboveClassVO;
use Tests\Orisai\ObjectMapper\Doubles\Invalid\UnsupportedClassDefinitionVO;
use Tests\Orisai\ObjectMapper\Doubles\Invalid\UnsupportedPropertyDefinitionVO;
use Tests\Orisai\ObjectMapper\Doubles\Invalid\VariantFieldChildVO;
use Tests\Orisai\ObjectMapper\Doubles\Invalid\VariantFieldVO;

final class ReflectorMetaSourceTest extends TestCase
{

	private ReflectorMetaSource $source;

	protected function setUp(): void
	{
		$this->source = new AnnotationsMetaSource();
	}

	/**
	 * @param ReflectionClass<covariant MappedObject> $class
	 */
	private function createStructureGroup(ReflectionClass $class): StructureGroup
	{
		return StructureGrouper::group(
			StructureFlattener::flatten(
				StructureBuilder::build($class),
			),
		);
	}

	/**
	 * @param class-string<MappedObject> $class
	 *
	 * @dataProvider provideUnsupportedDefinitionType
	 */
	public function testUnsupportedDefinitionType(string $class): void
	{
		$reflector = new ReflectionClass($class);
		$group = $this->createStructureGroup($reflector);

		$this->expectException(InvalidArgument::class);
		$this->expectExceptionMessage(
			"Definition 'Tests\Orisai\ObjectMapper\Doubles\Definition\UnsupportedDefinition' "
			. "(subtype of 'Orisai\ObjectMapper\Meta\MetaDefinition') should implement "
			. "'Orisai\ObjectMapper\Callbacks\CallbackDefinition', "
			. "'Orisai\ObjectMapper\Docs\DocDefinition', "
			. "'Orisai\ObjectMapper\Modifiers\ModifierDefinition' or "
			. "'Orisai\ObjectMapper\Rules\RuleDefinition'.",
		);

		$this->source->load($reflector, $group);
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
		$reflector = new ReflectionClass(VariantFieldVO::class);
		$group = $this->createStructureGroup($reflector);

		$this->expectException(InvalidArgument::class);
		$this->expectExceptionMessage(
			<<<'MSG'
Context: Resolving metadata of mapped object
         'Tests\Orisai\ObjectMapper\Doubles\Invalid\VariantFieldVO'.
Problem: Definition in annotation of property '$field' differs from definition
         in annotation of property
         'Tests\Orisai\ObjectMapper\Doubles\Invalid\VariantFieldParentVO->$field'.
Solution: Don't override metadata of properties in child classes.
MSG,
		);

		$this->source->load($reflector, $group);
	}

	public function testFieldInvarianceFullName(): void
	{
		$reflector = new ReflectionClass(VariantFieldChildVO::class);
		$group = $this->createStructureGroup($reflector);

		$this->expectException(InvalidArgument::class);
		$this->expectExceptionMessage(
			<<<'MSG'
Context: Resolving metadata of mapped object
         'Tests\Orisai\ObjectMapper\Doubles\Invalid\VariantFieldChildVO'.
Problem: Definition in annotation of property
         'Tests\Orisai\ObjectMapper\Doubles\Invalid\VariantFieldVO->$field'
         differs from definition in annotation of property
         'Tests\Orisai\ObjectMapper\Doubles\Invalid\VariantFieldParentVO->$field'.
Solution: Don't override metadata of properties in child classes.
MSG,
		);

		$this->source->load($reflector, $group);
	}

	public function testRuleAboveClassRelativeName(): void
	{
		$reflector = new ReflectionClass(RuleAboveClassVO::class);
		$group = $this->createStructureGroup($reflector);

		$this->expectException(InvalidArgument::class);
		$this->expectExceptionMessage(
			<<<'MSG'
Context: Resolving metadata of mapped object
         'Tests\Orisai\ObjectMapper\Doubles\Invalid\RuleAboveClassVO'.
Problem: Rule definition
         'Tests\Orisai\ObjectMapper\Doubles\Definition\TargetLessRuleDefinition'
         cannot be used on class, it is only allowed on properties.
MSG,
		);

		$this->source->load($reflector, $group);
	}

	public function testRuleAboveClassFullName(): void
	{
		$reflector = new ReflectionClass(RuleAboveClassChildVO::class);
		$group = $this->createStructureGroup($reflector);

		$this->expectException(InvalidArgument::class);
		$this->expectExceptionMessage(
			<<<'MSG'
Context: Resolving metadata of mapped object
         'Tests\Orisai\ObjectMapper\Doubles\Invalid\RuleAboveClassChildVO'.
Problem: Rule definition
         'Tests\Orisai\ObjectMapper\Doubles\Definition\TargetLessRuleDefinition'
         (used above class
         'Tests\Orisai\ObjectMapper\Doubles\Invalid\RuleAboveClassVO') cannot be
         used on class, it is only allowed on properties.
MSG,
		);

		$this->source->load($reflector, $group);
	}

	public function testFieldWithMultipleRulesRelativeName(): void
	{
		$reflector = new ReflectionClass(FieldWithMultipleRulesVO::class);
		$group = $this->createStructureGroup($reflector);

		$this->expectException(InvalidArgument::class);
		$this->expectExceptionMessage(
			<<<'MSG'
Context: Resolving metadata of mapped object
         'Tests\Orisai\ObjectMapper\Doubles\Invalid\FieldWithMultipleRulesVO'.
Problem: Property '$field' has multiple rule definitions (in annotation), but
         only one is allowed.
Solution: Combine multiple with 'Orisai\ObjectMapper\Rules\AnyOf' or
          'Orisai\ObjectMapper\Rules\AllOf'.
MSG,
		);

		$this->source->load($reflector, $group);
	}

	public function testFieldWithMultipleRulesAbsoluteName(): void
	{
		$reflector = new ReflectionClass(FieldWithMultipleRulesChildVO::class);
		$group = $this->createStructureGroup($reflector);

		$this->expectException(InvalidArgument::class);
		$this->expectExceptionMessage(
			<<<'MSG'
Context: Resolving metadata of mapped object
         'Tests\Orisai\ObjectMapper\Doubles\Invalid\FieldWithMultipleRulesChildVO'.
Problem: Property
         'Tests\Orisai\ObjectMapper\Doubles\Invalid\FieldWithMultipleRulesVO->$field'
         has multiple rule definitions (in annotation), but only one is allowed.
Solution: Combine multiple with 'Orisai\ObjectMapper\Rules\AnyOf' or
          'Orisai\ObjectMapper\Rules\AllOf'.
MSG,
		);

		$this->source->load($reflector, $group);
	}

	public function testFieldWithNoRuleRelativeName(): void
	{
		$reflector = new ReflectionClass(FieldWithNoRuleVO::class);
		$group = $this->createStructureGroup($reflector);

		$this->expectException(InvalidArgument::class);
		$this->expectExceptionMessage(
			<<<'MSG'
Context: Resolving metadata of mapped object
         'Tests\Orisai\ObjectMapper\Doubles\Invalid\FieldWithNoRuleVO'.
Problem: Property '$field' has some mapped object definition (in annotation),
         but no rule definition.
Solution: Either remove the definition or add a rule definition.
MSG,
		);

		$this->source->load($reflector, $group);
	}

	public function testFieldWithNoRuleAbsoluteName(): void
	{
		$reflector = new ReflectionClass(FieldWithNoRuleChildVO::class);
		$group = $this->createStructureGroup($reflector);

		$this->expectException(InvalidArgument::class);
		$this->expectExceptionMessage(
			<<<'MSG'
Context: Resolving metadata of mapped object
         'Tests\Orisai\ObjectMapper\Doubles\Invalid\FieldWithNoRuleChildVO'.
Problem: Property
         'Tests\Orisai\ObjectMapper\Doubles\Invalid\FieldWithNoRuleVO->$field'
         has some mapped object definition (in annotation), but no rule
         definition.
Solution: Either remove the definition or add a rule definition.
MSG,
		);

		$this->source->load($reflector, $group);
	}

}
