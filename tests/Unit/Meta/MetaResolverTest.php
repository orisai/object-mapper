<?php declare(strict_types = 1);

namespace Tests\Orisai\ObjectMapper\Unit\Meta;

use Generator;
use Orisai\Exceptions\Logic\InvalidArgument;
use Orisai\Exceptions\Logic\InvalidState;
use Orisai\ObjectMapper\MappedObject;
use Tests\Orisai\ObjectMapper\Doubles\FieldNames\ChildCollidingFieldVO;
use Tests\Orisai\ObjectMapper\Doubles\FieldNames\ChildFieldVO;
use Tests\Orisai\ObjectMapper\Doubles\FieldNames\FieldNameIdenticalWithAnotherPropertyNameVO;
use Tests\Orisai\ObjectMapper\Doubles\FieldNames\FieldNamesFromTraitVO;
use Tests\Orisai\ObjectMapper\Doubles\FieldNames\MultipleIdenticalFieldNamesVO;
use Tests\Orisai\ObjectMapper\Doubles\Meta\ClassInterfaceMetaInvalidScopeRootVO;
use Tests\Orisai\ObjectMapper\Doubles\Meta\ClassMetaInvalidScopeRootVO;
use Tests\Orisai\ObjectMapper\Doubles\Meta\ClassTraitMetaInvalidScopeRootVO;
use Tests\Orisai\ObjectMapper\Doubles\Meta\FieldMetaInvalidScopeRootVO;
use Tests\Orisai\ObjectMapper\Doubles\Meta\FieldTraitMetaInvalidScopeRootVO;
use Tests\Orisai\ObjectMapper\Doubles\Meta\StaticMappedPropertyVO;
use Tests\Orisai\ObjectMapper\Doubles\Meta\WrongArgsTypeRule;
use Tests\Orisai\ObjectMapper\Doubles\Meta\WrongArgsTypeVO;
use Tests\Orisai\ObjectMapper\Toolkit\ProcessingTestCase;

final class MetaResolverTest extends ProcessingTestCase
{

	public function testStaticMappedProperty(): void
	{
		$this->expectException(InvalidArgument::class);
		$this->expectExceptionMessage(
			<<<'MSG'
Context: Resolving metadata of mapped object
         'Tests\Orisai\ObjectMapper\Doubles\Meta\StaticMappedPropertyVO'.
Problem: Mapped property
         Tests\Orisai\ObjectMapper\Doubles\Meta\StaticMappedPropertyTraitVO::$field
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
         'Tests\Orisai\ObjectMapper\Doubles\Meta\ClassMetaInvalidScopeRootVO'.
Problem: Class 'Tests\Orisai\ObjectMapper\Doubles\Meta\ClassMetaInvalidScopeVO'
         defines metadata, but does not implement mapped object.
Solution: Implement the 'Orisai\ObjectMapper\MappedObject' interface.
MSG,
		];

		yield [
			ClassInterfaceMetaInvalidScopeRootVO::class,
			<<<'MSG'
Context: Resolving metadata of mapped object
         'Tests\Orisai\ObjectMapper\Doubles\Meta\ClassInterfaceMetaInvalidScopeRootVO'.
Problem: Interface
         'Tests\Orisai\ObjectMapper\Doubles\Meta\ClassInterfaceMetaInvalidScopeInterfaceVO'
         defines metadata, but does not extend mapped object.
Solution: Extend the 'Orisai\ObjectMapper\MappedObject' interface.
MSG,
		];

		yield [
			ClassTraitMetaInvalidScopeRootVO::class,
			<<<'MSG'
Context: Resolving metadata of mapped object
         'Tests\Orisai\ObjectMapper\Doubles\Meta\ClassTraitMetaInvalidScopeRootVO'.
Problem: Trait
         'Tests\Orisai\ObjectMapper\Doubles\Meta\ClassTraitMetaInvalidScopeTraitVO'
         defines metadata, but is used in class
         'Tests\Orisai\ObjectMapper\Doubles\Meta\ClassTraitMetaInvalidScopeVO'
         which does not implement mapped object.
Solution: Implement the 'Orisai\ObjectMapper\MappedObject' interface.
MSG,
		];

		yield [
			FieldMetaInvalidScopeRootVO::class,
			<<<'MSG'
Context: Resolving metadata of mapped object
         'Tests\Orisai\ObjectMapper\Doubles\Meta\FieldMetaInvalidScopeRootVO'.
Problem: Property
         'Tests\Orisai\ObjectMapper\Doubles\Meta\FieldMetaInvalidScopeVO->$field'
         defines metadata, but the class
         'Tests\Orisai\ObjectMapper\Doubles\Meta\FieldMetaInvalidScopeVO' does
         not implement mapped object.
Solution: Implement the 'Orisai\ObjectMapper\MappedObject' interface.
MSG,
		];

		yield [
			FieldTraitMetaInvalidScopeRootVO::class,
			<<<'MSG'
Context: Resolving metadata of mapped object
         'Tests\Orisai\ObjectMapper\Doubles\Meta\FieldTraitMetaInvalidScopeRootVO'.
Problem: Property
         'Tests\Orisai\ObjectMapper\Doubles\Meta\FieldTraitMetaInvalidScopeTraitVO->$field'
         defines metadata, but its trait is used in class
         'Tests\Orisai\ObjectMapper\Doubles\Meta\FieldTraitMetaInvalidScopeVO'
         which does not implement mapped object.
Solution: Implement the 'Orisai\ObjectMapper\MappedObject' interface.
MSG,
		];
	}

	public function testMultipleIdenticalFieldNames(): void
	{
		$this->expectException(InvalidState::class);
		$this->expectExceptionMessage(
			<<<'TXT'
Context: Resolving metadata of mapped object
         'Tests\Orisai\ObjectMapper\Doubles\FieldNames\MultipleIdenticalFieldNamesVO'.
Problem: Properties
         'Tests\Orisai\ObjectMapper\Doubles\FieldNames\MultipleIdenticalFieldNamesVO->$property2'
         and
         'Tests\Orisai\ObjectMapper\Doubles\FieldNames\MultipleIdenticalFieldNamesVO->$property1'
         have conflicting field name 'field'.
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
         'Tests\Orisai\ObjectMapper\Doubles\FieldNames\FieldNameIdenticalWithAnotherPropertyNameVO'.
Problem: Properties
         'Tests\Orisai\ObjectMapper\Doubles\FieldNames\FieldNameIdenticalWithAnotherPropertyNameVO->$property'
         and
         'Tests\Orisai\ObjectMapper\Doubles\FieldNames\FieldNameIdenticalWithAnotherPropertyNameVO->$field'
         have conflicting field name 'field'.
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
         'Tests\Orisai\ObjectMapper\Doubles\FieldNames\ChildCollidingFieldVO'.
Problem: Properties
         'Tests\Orisai\ObjectMapper\Doubles\FieldNames\ChildCollidingFieldVO->$property'
         and
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
         'Tests\Orisai\ObjectMapper\Doubles\FieldNames\FieldNamesFromTraitVO'.
Problem: Properties
         'Tests\Orisai\ObjectMapper\Doubles\FieldNames\FieldNamesTrait2->$property2'
         and
         'Tests\Orisai\ObjectMapper\Doubles\FieldNames\FieldNamesTrait1->$property1'
         have conflicting field name 'field'.
Solution: Define unique field name for each mapped property.
TXT,
		);

		$this->metaLoader->load(FieldNamesFromTraitVO::class);
	}

	public function testNotMatchingArgsType(): void
	{
		$this->ruleManager->addRule(new WrongArgsTypeRule());

		$this->expectException(InvalidArgument::class);
		$this->expectExceptionMessage(
			"'Tests\Orisai\ObjectMapper\Doubles\Meta\WrongArgsTypeRule->resolveArgs()' should return 'nonsense'"
			. " (as defined in 'getArgsType()' method), but returns 'Orisai\ObjectMapper\Args\EmptyArgs'.",
		);

		$this->metaLoader->load(WrongArgsTypeVO::class);
	}

}
