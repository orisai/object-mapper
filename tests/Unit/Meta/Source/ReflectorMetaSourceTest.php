<?php declare(strict_types = 1);

namespace Tests\Orisai\ObjectMapper\Unit\Meta\Source;

use Generator;
use Orisai\Exceptions\Logic\InvalidArgument;
use Orisai\ObjectMapper\MappedObject;
use Orisai\ObjectMapper\Meta\Source\AnnotationsMetaSource;
use Orisai\ObjectMapper\Meta\Source\AttributesMetaSource;
use Orisai\ObjectMapper\Meta\Source\ReflectorMetaSource;
use Orisai\ReflectionMeta\Structure\StructureBuilder;
use Orisai\ReflectionMeta\Structure\StructureFlattener;
use Orisai\ReflectionMeta\Structure\StructureGroup;
use Orisai\ReflectionMeta\Structure\StructureGrouper;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use Tests\Orisai\ObjectMapper\Doubles\Invalid\DefinitionAboveConstantVO;
use Tests\Orisai\ObjectMapper\Doubles\Invalid\DefinitionAboveMethodVO;
use Tests\Orisai\ObjectMapper\Doubles\Invalid\DefinitionAboveParameterVO;
use Tests\Orisai\ObjectMapper\Doubles\Invalid\RuleAboveClassChildVO;
use Tests\Orisai\ObjectMapper\Doubles\Invalid\RuleAboveClassVO;
use Tests\Orisai\ObjectMapper\Doubles\Invalid\UnsupportedClassDefinitionVO;
use Tests\Orisai\ObjectMapper\Doubles\Invalid\UnsupportedPropertyDefinitionVO;
use const PHP_VERSION_ID;

final class ReflectorMetaSourceTest extends TestCase
{

	private ReflectorMetaSource $annotationsSource;

	private ReflectorMetaSource $attributesSource;

	protected function setUp(): void
	{
		$this->annotationsSource = new AnnotationsMetaSource();

		if (PHP_VERSION_ID >= 8_00_00) {
			$this->attributesSource = new AttributesMetaSource();
		}
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
	 * @dataProvider provideUnsupportedDefinition
	 */
	public function testUnsupportedDefinitionLocation(string $class, string $errorMessage): void
	{
		if (PHP_VERSION_ID < 8_00_00) {
			self::markTestSkipped('Attributes are supported on PHP 8.0+');
		}

		$reflector = new ReflectionClass($class);
		$group = $this->createStructureGroup($reflector);

		$this->expectException(InvalidArgument::class);
		$this->expectExceptionMessage($errorMessage);

		$this->attributesSource->load($reflector, $group);
	}

	public function provideUnsupportedDefinition(): Generator
	{
		yield [
			DefinitionAboveConstantVO::class,
			<<<'MSG'
Context: Resolving metadata of mapped object
         'Tests\Orisai\ObjectMapper\Doubles\Invalid\DefinitionAboveConstantVO'.
Problem: Definitions are not allowed on constants.
Solution: Remove definition from
          'Tests\Orisai\ObjectMapper\Doubles\Invalid\DefinitionAboveConstantVO::Test'.
MSG,
		];

		yield [
			DefinitionAboveMethodVO::class,
			<<<'MSG'
Context: Resolving metadata of mapped object
         'Tests\Orisai\ObjectMapper\Doubles\Invalid\DefinitionAboveMethodVO'.
Problem: Definitions are not allowed on methods.
Solution: Remove definition from
          'Tests\Orisai\ObjectMapper\Doubles\Invalid\DefinitionAboveMethodVO->test()'.
MSG,
		];

		yield [
			DefinitionAboveParameterVO::class,
			<<<'MSG'
Context: Resolving metadata of mapped object
         'Tests\Orisai\ObjectMapper\Doubles\Invalid\DefinitionAboveParameterVO'.
Problem: Definitions are not allowed on parameters.
Solution: Remove definition from
          'Tests\Orisai\ObjectMapper\Doubles\Invalid\DefinitionAboveParameterVO->test(test)'.
MSG,
		];
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

		$this->annotationsSource->load($reflector, $group);
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

		$this->annotationsSource->load($reflector, $group);
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

		$this->annotationsSource->load($reflector, $group);
	}

}
