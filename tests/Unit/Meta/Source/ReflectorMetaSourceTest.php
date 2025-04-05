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
use Tests\Orisai\ObjectMapper\Doubles\Invalid\RuleAboveClassChildVO;
use Tests\Orisai\ObjectMapper\Doubles\Invalid\RuleAboveClassVO;
use Tests\Orisai\ObjectMapper\Doubles\Invalid\UnsupportedClassDefinitionVO;
use Tests\Orisai\ObjectMapper\Doubles\Invalid\UnsupportedPropertyDefinitionVO;

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

}
