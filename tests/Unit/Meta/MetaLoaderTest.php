<?php declare(strict_types = 1);

namespace Tests\Orisai\ObjectMapper\Unit\Meta;

use Orisai\Exceptions\Logic\InvalidState;
use stdClass;
use Tests\Orisai\ObjectMapper\Doubles\Dependencies\DependenciesUsingVoInjector;
use Tests\Orisai\ObjectMapper\Doubles\Dependencies\DependentBaseVoInjector;
use Tests\Orisai\ObjectMapper\Doubles\Dependencies\DependentChildVoInjector1;
use Tests\Orisai\ObjectMapper\Doubles\Dependencies\DependentChildVoInjector2;
use Tests\Orisai\ObjectMapper\Doubles\FieldNames\ChildCollidingFieldVO;
use Tests\Orisai\ObjectMapper\Doubles\FieldNames\ChildFieldVO;
use Tests\Orisai\ObjectMapper\Doubles\FieldNames\FieldNameIdenticalWithAnotherPropertyNameVO;
use Tests\Orisai\ObjectMapper\Doubles\FieldNames\MultipleIdenticalFieldNamesVO;
use Tests\Orisai\ObjectMapper\Toolkit\ProcessingTestCase;
use const PHP_VERSION_ID;

final class MetaLoaderTest extends ProcessingTestCase
{

	public function testMultipleIdenticalFieldNames(): void
	{
		$this->expectException(InvalidState::class);
		$this->expectExceptionMessage(
			<<<'TXT'
Context: Validating mapped property
         'Tests\Orisai\ObjectMapper\Doubles\FieldNames\MultipleIdenticalFieldNamesVO::$property2'.
Problem: Field name 'field' defined in field name meta collides with field name
         of property
         'Tests\Orisai\ObjectMapper\Doubles\FieldNames\MultipleIdenticalFieldNamesVO::$property1'
         defined in field name meta.
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
Context: Validating mapped property
         'Tests\Orisai\ObjectMapper\Doubles\FieldNames\FieldNameIdenticalWithAnotherPropertyNameVO::$property'.
Problem: Field name 'field' defined in field name meta collides with field name
         of property
         'Tests\Orisai\ObjectMapper\Doubles\FieldNames\FieldNameIdenticalWithAnotherPropertyNameVO::$field'
         defined in property name.
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
Context: Validating mapped property
         'Tests\Orisai\ObjectMapper\Doubles\FieldNames\ChildCollidingFieldVO::$property'.
Problem: Field name 'property' defined in property name collides with field name
         of property
         'Tests\Orisai\ObjectMapper\Doubles\FieldNames\ParentFieldVO::$property'
         defined in property name.
Solution: Define unique field name for each mapped property.
TXT,
		);

		$this->metaLoader->load(ChildCollidingFieldVO::class);
	}

	/**
	 * @runInSeparateProcess
	 */
	public function testPreload(): void
	{
		$manager = $this->dependencies->dependencyInjectorManager;
		$manager->addInjector(new DependenciesUsingVoInjector(new stdClass()));
		$manager->addInjector(new DependentBaseVoInjector(new stdClass()));
		$manager->addInjector(new DependentChildVoInjector1('string'));
		$manager->addInjector(new DependentChildVoInjector2(123));

		$excludes = [];
		$excludes[] = __DIR__ . '/../../Doubles/FieldNames/FieldNameIdenticalWithAnotherPropertyNameVO.php';
		$excludes[] = __DIR__ . '/../../Doubles/FieldNames/MultipleIdenticalFieldNamesVO.php';
		$excludes[] = __DIR__ . '/../../Doubles/FieldNames/ChildCollidingFieldVO.php';
		$excludes[] = __DIR__ . '/../../Doubles/Constructing/DependentVO.php';

		if (PHP_VERSION_ID < 8_00_00) {
			$excludes[] = __DIR__ . '/../../Doubles/PhpVersionSpecific/AttributesVO.php';
		}

		if (PHP_VERSION_ID < 8_01_00) {
			$excludes[] = __DIR__ . '/../../Doubles/Callbacks/ObjectInitializingVoPhp81.php';
			$excludes[] = __DIR__ . '/../../Doubles/Enums';
			$excludes[] = __DIR__ . '/../../Doubles/PhpVersionSpecific/ConstructorPromotedVO.php';
			$excludes[] = __DIR__ . '/../../Doubles/PhpVersionSpecific/ObjectDefaultVO.php';
			$excludes[] = __DIR__ . '/../../Doubles/PhpVersionSpecific/ReadonlyPropertiesVO.php';
		}

		if (PHP_VERSION_ID < 8_02_00) {
			$excludes[] = __DIR__ . '/../../Doubles/PhpVersionSpecific/ReadonlyClassVO.php';
		}

		$this->metaLoader->preloadFromPaths(
			[
				__DIR__ . '/../../Doubles',
			],
			$excludes,
		);

		// Makes PHPUnit happy
		self::assertTrue(true);
	}

}
