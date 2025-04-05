<?php declare(strict_types = 1);

namespace Tests\Orisai\ObjectMapper\Unit\Meta;

use Generator;
use Orisai\Exceptions\Logic\InvalidArgument;
use Orisai\Exceptions\Logic\InvalidState;
use Orisai\ObjectMapper\MappedObject;
use Tests\Orisai\ObjectMapper\Doubles\FieldNames\ChildFieldVO;
use Tests\Orisai\ObjectMapper\Doubles\Invalid\ChildCollidingFieldVO;
use Tests\Orisai\ObjectMapper\Doubles\Invalid\ClassInterfaceMetaInvalidScopeRootVO;
use Tests\Orisai\ObjectMapper\Doubles\Invalid\ClassMetaInvalidScopeRootVO;
use Tests\Orisai\ObjectMapper\Doubles\Invalid\ClassTraitMetaInvalidScopeRootVO;
use Tests\Orisai\ObjectMapper\Doubles\Invalid\FieldMetaInvalidScopeRootVO;
use Tests\Orisai\ObjectMapper\Doubles\Invalid\FieldNameIdenticalWithAnotherPropertyNameVO;
use Tests\Orisai\ObjectMapper\Doubles\Invalid\FieldNamesFromTraitVO;
use Tests\Orisai\ObjectMapper\Doubles\Invalid\FieldTraitMetaInvalidScopeRootVO;
use Tests\Orisai\ObjectMapper\Doubles\Invalid\FieldWithNoRuleChildVO;
use Tests\Orisai\ObjectMapper\Doubles\Invalid\FieldWithNoRuleVO;
use Tests\Orisai\ObjectMapper\Doubles\Invalid\MultipleIdenticalFieldNamesVO;
use Tests\Orisai\ObjectMapper\Doubles\Invalid\StaticMappedPropertyVO;
use Tests\Orisai\ObjectMapper\Doubles\Invalid\VariantFieldChildVO;
use Tests\Orisai\ObjectMapper\Doubles\Invalid\VariantFieldVO;
use Tests\Orisai\ObjectMapper\Doubles\Invalid\WrongCallbackArgsTypeVO;
use Tests\Orisai\ObjectMapper\Doubles\Invalid\WrongRuleArgsTypeVO;
use Tests\Orisai\ObjectMapper\Doubles\Rules\WrongArgsTypeRule;
use Tests\Orisai\ObjectMapper\Toolkit\ProcessingTestCase;

final class MetaResolverTest extends ProcessingTestCase
{

	public function testStaticMappedProperty(): void
	{
		$this->expectException(InvalidArgument::class);
		$this->expectExceptionMessage(
			<<<'MSG'
Context: Resolving metadata of mapped object
         'Tests\Orisai\ObjectMapper\Doubles\Invalid\StaticMappedPropertyVO'.
Problem: Mapped property
         Tests\Orisai\ObjectMapper\Doubles\Invalid\StaticMappedPropertyTraitVO::$field
         is static, but static properties are not supported.
Solution: Make the property non-static.
MSG,
		);

		$this->metaLoader->load(StaticMappedPropertyVO::class);
	}

	/**
	 * @param class-string<MappedObject> $class
	 *
	 * @dataProvider provideMetaOutOfScope
	 */
	public function testMetaOutOfScope(string $class, string $exceptionMessage): void
	{
		$this->expectException(InvalidArgument::class);
		$this->expectExceptionMessage($exceptionMessage);

		$this->metaLoader->load($class);
	}

	public function provideMetaOutOfScope(): Generator
	{
		yield [
			ClassMetaInvalidScopeRootVO::class,
			<<<'MSG'
Context: Resolving metadata of mapped object
         'Tests\Orisai\ObjectMapper\Doubles\Invalid\ClassMetaInvalidScopeRootVO'.
Problem: Class
         'Tests\Orisai\ObjectMapper\Doubles\Invalid\ClassMetaInvalidScopeVO'
         defines metadata, but does not implement mapped object.
Solution: Implement the 'Orisai\ObjectMapper\MappedObject' interface.
MSG,
		];

		yield [
			ClassInterfaceMetaInvalidScopeRootVO::class,
			<<<'MSG'
Context: Resolving metadata of mapped object
         'Tests\Orisai\ObjectMapper\Doubles\Invalid\ClassInterfaceMetaInvalidScopeRootVO'.
Problem: Interface
         'Tests\Orisai\ObjectMapper\Doubles\Invalid\ClassInterfaceMetaInvalidScopeInterfaceVO'
         defines metadata, but does not extend mapped object.
Solution: Extend the 'Orisai\ObjectMapper\MappedObject' interface.
MSG,
		];

		yield [
			ClassTraitMetaInvalidScopeRootVO::class,
			<<<'MSG'
Context: Resolving metadata of mapped object
         'Tests\Orisai\ObjectMapper\Doubles\Invalid\ClassTraitMetaInvalidScopeRootVO'.
Problem: Trait
         'Tests\Orisai\ObjectMapper\Doubles\Invalid\ClassTraitMetaInvalidScopeTraitVO'
         defines metadata, but is used in class
         'Tests\Orisai\ObjectMapper\Doubles\Invalid\ClassTraitMetaInvalidScopeVO'
         which does not implement mapped object.
Solution: Implement the 'Orisai\ObjectMapper\MappedObject' interface.
MSG,
		];

		yield [
			FieldMetaInvalidScopeRootVO::class,
			<<<'MSG'
Context: Resolving metadata of mapped object
         'Tests\Orisai\ObjectMapper\Doubles\Invalid\FieldMetaInvalidScopeRootVO'.
Problem: Property
         'Tests\Orisai\ObjectMapper\Doubles\Invalid\FieldMetaInvalidScopeVO->$field'
         defines metadata, but the class
         'Tests\Orisai\ObjectMapper\Doubles\Invalid\FieldMetaInvalidScopeVO'
         does not implement mapped object.
Solution: Implement the 'Orisai\ObjectMapper\MappedObject' interface.
MSG,
		];

		yield [
			FieldTraitMetaInvalidScopeRootVO::class,
			<<<'MSG'
Context: Resolving metadata of mapped object
         'Tests\Orisai\ObjectMapper\Doubles\Invalid\FieldTraitMetaInvalidScopeRootVO'.
Problem: Property
         'Tests\Orisai\ObjectMapper\Doubles\Invalid\FieldTraitMetaInvalidScopeTraitVO->$field'
         defines metadata, but its trait is used in class
         'Tests\Orisai\ObjectMapper\Doubles\Invalid\FieldTraitMetaInvalidScopeVO'
         which does not implement mapped object.
Solution: Implement the 'Orisai\ObjectMapper\MappedObject' interface.
MSG,
		];
	}

	public function testFieldInvarianceRelativeName(): void
	{
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

		$this->metaLoader->load(VariantFieldVO::class);
	}

	public function testFieldInvarianceFullName(): void
	{
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

		$this->metaLoader->load(VariantFieldChildVO::class);
	}

	public function testFieldWithNoRuleRelativeName(): void
	{
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

		$this->metaLoader->load(FieldWithNoRuleVO::class);
	}

	public function testFieldWithNoRuleAbsoluteName(): void
	{
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

		$this->metaLoader->load(FieldWithNoRuleChildVO::class);
	}

	public function testMultipleIdenticalFieldNames(): void
	{
		$this->expectException(InvalidState::class);
		$this->expectExceptionMessage(
			<<<'TXT'
Context: Resolving metadata of mapped object
         'Tests\Orisai\ObjectMapper\Doubles\Invalid\MultipleIdenticalFieldNamesVO'.
Problem: Properties '$property2' and '$property1' have conflicting field name
         'field'.
Solution: Define unique field name for each mapped property.
TXT,
		);

		$this->metaLoader->load(MultipleIdenticalFieldNamesVO::class);
	}

	public function testFieldNameIdenticalWithAnotherPropertyName(): void
	{
		$this->expectException(InvalidState::class);
		$this->expectExceptionMessage(
			<<<'TXT'
Context: Resolving metadata of mapped object
         'Tests\Orisai\ObjectMapper\Doubles\Invalid\FieldNameIdenticalWithAnotherPropertyNameVO'.
Problem: Properties '$property' and '$field' have conflicting field name
         'field'.
Solution: Define unique field name for each mapped property.
TXT,
		);

		$this->metaLoader->load(FieldNameIdenticalWithAnotherPropertyNameVO::class);
	}

	public function testMultipleIdenticalPropertyNames(): void
	{
		// Is okay
		$this->metaLoader->load(ChildFieldVO::class);

		$this->expectException(InvalidState::class);
		$this->expectExceptionMessage(
			<<<'TXT'
Context: Resolving metadata of mapped object
         'Tests\Orisai\ObjectMapper\Doubles\Invalid\ChildCollidingFieldVO'.
Problem: Properties '$property' and
         'Tests\Orisai\ObjectMapper\Doubles\FieldNames\ParentFieldVO->$property'
         have conflicting field name 'property'.
Solution: Define unique field name for each mapped property.
TXT,
		);

		$this->metaLoader->load(ChildCollidingFieldVO::class);
	}

	public function testFieldNamesFromTrait(): void
	{
		$this->expectException(InvalidState::class);
		$this->expectExceptionMessage(
			<<<'TXT'
Context: Resolving metadata of mapped object
         'Tests\Orisai\ObjectMapper\Doubles\Invalid\FieldNamesFromTraitVO'.
Problem: Properties
         'Tests\Orisai\ObjectMapper\Doubles\Invalid\FieldNamesTrait2->$property2'
         and
         'Tests\Orisai\ObjectMapper\Doubles\Invalid\FieldNamesTrait1->$property1'
         have conflicting field name 'field'.
Solution: Define unique field name for each mapped property.
TXT,
		);

		$this->metaLoader->load(FieldNamesFromTraitVO::class);
	}

	public function testWrongRuleArgsType(): void
	{
		$this->ruleManager->addRule(new WrongArgsTypeRule());

		$this->expectException(InvalidArgument::class);
		$this->expectExceptionMessage(
			"'Tests\Orisai\ObjectMapper\Doubles\Rules\WrongArgsTypeRule->resolveArgs()'"
			. " should return 'Orisai\ObjectMapper\Args\EmptyArgs'"
			. " (as defined in 'getArgsType()' method),"
			. " but returns 'Orisai\ObjectMapper\Rules\NullArgs'.",
		);

		$this->metaLoader->load(WrongRuleArgsTypeVO::class);
	}

	public function testWrongCallbackArgsType(): void
	{
		$this->expectException(InvalidArgument::class);
		$this->expectExceptionMessage(
			"'Tests\Orisai\ObjectMapper\Doubles\Callbacks\WrongArgsTypeCallback::resolveArgs()'"
			. " should return 'Orisai\ObjectMapper\Args\EmptyArgs'"
			. " (as defined in 'getArgsType()' method),"
			. " but returns 'Orisai\ObjectMapper\Rules\NullArgs'.",
		);

		$this->metaLoader->load(WrongCallbackArgsTypeVO::class);
	}

}
