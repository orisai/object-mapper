<?php declare(strict_types = 1);

namespace Tests\Orisai\ObjectMapper\Unit\Meta;

use stdClass;
use Tests\Orisai\ObjectMapper\Doubles\Dependencies\DependenciesUsingVoInjector;
use Tests\Orisai\ObjectMapper\Doubles\Dependencies\DependentBaseVoInjector;
use Tests\Orisai\ObjectMapper\Doubles\Dependencies\DependentChildVoInjector1;
use Tests\Orisai\ObjectMapper\Doubles\Dependencies\DependentChildVoInjector2;
use Tests\Orisai\ObjectMapper\Toolkit\ProcessingTestCase;
use const PHP_VERSION_ID;

final class MetaLoaderTest extends ProcessingTestCase
{

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
		$excludes[] = __DIR__ . '/../../Doubles/Meta/ClassMetaInvalidScopeRootVO.php';
		$excludes[] = __DIR__ . '/../../Doubles/Meta/ClassInterfaceMetaInvalidScopeRootVO.php';
		$excludes[] = __DIR__ . '/../../Doubles/Meta/ClassTraitMetaInvalidScopeRootVO.php';
		$excludes[] = __DIR__ . '/../../Doubles/Meta/FieldMetaInvalidScopeRootVO.php';
		$excludes[] = __DIR__ . '/../../Doubles/Meta/FieldTraitMetaInvalidScopeRootVO.php';
		$excludes[] = __DIR__ . '/../../Doubles/Meta/StaticMappedPropertyVO.php';

		if (PHP_VERSION_ID < 8_00_00) {
			$excludes[] = __DIR__ . '/../../Doubles/PhpVersionSpecific/AttributesVO.php';
			$excludes[] = __DIR__ . '/../../Doubles/PhpVersionSpecific/ConstructorPromotedVO.php';
			$excludes[] = __DIR__ . '/../../Doubles/PhpVersionSpecific/DefaultsOverrideVO.php';
		}

		if (PHP_VERSION_ID < 8_01_00) {
			$excludes[] = __DIR__ . '/../../Doubles/Callbacks/ObjectInitializingVoPhp81.php';
			$excludes[] = __DIR__ . '/../../Doubles/Enums';
			$excludes[] = __DIR__ . '/../../Doubles/PhpVersionSpecific/NewInInitializersVO.php';
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
		/** @phpstan-ignore-next-line */
		self::assertTrue(true);
	}

}
