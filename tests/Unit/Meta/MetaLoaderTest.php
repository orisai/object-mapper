<?php declare(strict_types = 1);

namespace Tests\Orisai\ObjectMapper\Unit\Meta;

use Orisai\Exceptions\Logic\InvalidArgument;
use stdClass;
use Tests\Orisai\ObjectMapper\Doubles\Dependencies\DependenciesUsingVoInjector;
use Tests\Orisai\ObjectMapper\Doubles\Dependencies\DependentBaseVoInjector;
use Tests\Orisai\ObjectMapper\Doubles\Dependencies\DependentChildVoInjector1;
use Tests\Orisai\ObjectMapper\Doubles\Dependencies\DependentChildVoInjector2;
use Tests\Orisai\ObjectMapper\Doubles\Invalid\EnumVO;
use Tests\Orisai\ObjectMapper\Doubles\Meta\AbstractVO;
use Tests\Orisai\ObjectMapper\Doubles\Meta\InterfaceVO;
use Tests\Orisai\ObjectMapper\Toolkit\ProcessingTestCase;
use const PHP_VERSION_ID;

final class MetaLoaderTest extends ProcessingTestCase
{

	public function testNotAClass(): void
	{
		$this->expectException(InvalidArgument::class);
		$this->expectExceptionMessage("Class 'foo' does not exist.");

		/** @phpstan-ignore-next-line */
		$this->metaLoader->load('foo');
	}

	public function testNotAMappedObject(): void
	{
		$this->expectException(InvalidArgument::class);
		$this->expectExceptionMessage(
			<<<'TXT'
Context: Resolving metadata of mapped object 'stdClass'.
Problem: Class does not implement interface of mapped object.
Solution: Implement the 'Orisai\ObjectMapper\MappedObject' interface.
TXT,
		);

		/** @phpstan-ignore-next-line */
		$this->metaLoader->load(stdClass::class);
	}

	public function testAbstractClass(): void
	{
		$this->expectException(InvalidArgument::class);
		$this->expectExceptionMessage(
			<<<'TXT'
Context: Resolving metadata of mapped object
         'Tests\Orisai\ObjectMapper\Doubles\Meta\AbstractVO'.
Problem: 'Tests\Orisai\ObjectMapper\Doubles\Meta\AbstractVO' is abstract.
Solution: Load metadata only for non-abstract classes.
TXT,
		);

		$this->metaLoader->load(AbstractVO::class);
	}

	public function testInterface(): void
	{
		$this->expectException(InvalidArgument::class);
		$this->expectExceptionMessage(
			<<<'TXT'
Context: Resolving metadata of mapped object
         'Tests\Orisai\ObjectMapper\Doubles\Meta\InterfaceVO'.
Problem: 'Tests\Orisai\ObjectMapper\Doubles\Meta\InterfaceVO' is an interface.
Solution: Load metadata only for classes.
TXT,
		);

		$this->metaLoader->load(InterfaceVO::class);
	}

	public function testEnum(): void
	{
		if (PHP_VERSION_ID < 8_01_00) {
			self::markTestSkipped('Enums are available on PHP 8.1+');
		}

		$this->expectException(InvalidArgument::class);
		$this->expectExceptionMessage(
			<<<'TXT'
Context: Resolving metadata of mapped object
         'Tests\Orisai\ObjectMapper\Doubles\Invalid\EnumVO'.
Problem: Mapped object can't be an enum.
TXT,
		);

		$this->metaLoader->load(EnumVO::class);
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
		$excludes[] = __DIR__ . '/../../Doubles/Invalid';

		if (PHP_VERSION_ID < 8_00_00) {
			$excludes[] = __DIR__ . '/../../Doubles/Php80';
		}

		if (PHP_VERSION_ID < 8_01_00) {
			$excludes[] = __DIR__ . '/../../Doubles/Php81';
		}

		if (PHP_VERSION_ID < 8_02_00) {
			$excludes[] = __DIR__ . '/../../Doubles/Php82';
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
