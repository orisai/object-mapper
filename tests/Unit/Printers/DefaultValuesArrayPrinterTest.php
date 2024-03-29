<?php declare(strict_types = 1);

namespace Tests\Orisai\ObjectMapper\Unit\Printers;

use Orisai\ObjectMapper\Context\TypeContext;
use Orisai\ObjectMapper\MappedObject;
use Orisai\ObjectMapper\Meta\Cache\ArrayMetaCache;
use Orisai\ObjectMapper\Meta\MetaLoader;
use Orisai\ObjectMapper\Meta\MetaResolverFactory;
use Orisai\ObjectMapper\Meta\Source\AnnotationsMetaSource;
use Orisai\ObjectMapper\Meta\Source\DefaultMetaSourceManager;
use Orisai\ObjectMapper\Printers\DefaultValuesArrayPrinter;
use Orisai\ObjectMapper\Processing\DefaultDependencyInjectorManager;
use Orisai\ObjectMapper\Processing\ObjectCreator;
use Orisai\ObjectMapper\Processing\Options;
use Orisai\ObjectMapper\Rules\DefaultRuleManager;
use Orisai\ObjectMapper\Rules\MappedObjectArgs;
use Orisai\ObjectMapper\Rules\MappedObjectRule;
use Orisai\ObjectMapper\Rules\RuleManager;
use Orisai\ObjectMapper\Types\MappedObjectType;
use PHPUnit\Framework\TestCase;
use Tests\Orisai\ObjectMapper\Doubles\DefaultsVO;
use Tests\Orisai\ObjectMapper\Doubles\NoDefaultsVO;
use Tests\Orisai\ObjectMapper\Doubles\StructuresVO;

final class DefaultValuesArrayPrinterTest extends TestCase
{

	private DefaultValuesArrayPrinter $printer;

	private RuleManager $ruleManager;

	private MetaLoader $metaLoader;

	protected function setUp(): void
	{
		$this->ruleManager = new DefaultRuleManager();

		$sourceManager = new DefaultMetaSourceManager();
		$sourceManager->addSource(new AnnotationsMetaSource());

		$objectCreator = new ObjectCreator(new DefaultDependencyInjectorManager());
		$cache = new ArrayMetaCache();
		$resolverFactory = new MetaResolverFactory($this->ruleManager, $objectCreator);
		$this->metaLoader = new MetaLoader($cache, $sourceManager, $resolverFactory);
		$this->printer = new DefaultValuesArrayPrinter($this->metaLoader);
	}

	/**
	 * @param class-string<MappedObject> $class
	 */
	private function createType(string $class): MappedObjectType
	{
		return $this->ruleManager->getRule(MappedObjectRule::class)->createType(
			new MappedObjectArgs($class),
			new TypeContext($this->metaLoader, $this->ruleManager, new Options()),
		);
	}

	public function testDefaults(): void
	{
		$type = $this->createType(DefaultsVO::class);

		self::assertSame(
			[
				'string' => 'foo',
				'nullableString' => null,
				'arrayOfMixed' => [
					0 => 'foo',
					'bar' => 'baz',
				],
			],
			$this->printer->printType($type),
		);
	}

	public function testNoDefaults(): void
	{
		$type = $this->createType(NoDefaultsVO::class);

		self::assertSame(
			[
				'structure' => [
					'string' => 'foo',
					'nullableString' => null,
					'arrayOfMixed' => [
						0 => 'foo',
						'bar' => 'baz',
					],
				],
			],
			$this->printer->printType($type),
		);

		$this->printer->requiredValuePlaceholder = '__REQUIRED__';

		self::assertSame(
			[
				'string' => '__REQUIRED__',
				'nullableString' => '__REQUIRED__',
				'arrayOfMixed' => '__REQUIRED__',
				'structure' => [
					'string' => 'foo',
					'nullableString' => null,
					'arrayOfMixed' => [
						0 => 'foo',
						'bar' => 'baz',
					],
				],
				'manyStructures' => '__REQUIRED__',
			],
			$this->printer->printType($type),
		);
	}

	public function testCompound(): void
	{
		$type = $this->createType(StructuresVO::class);

		self::assertSame(
			[
				'structure' => [
					'string' => 'foo',
					'nullableString' => null,
					'arrayOfMixed' => [
						0 => 'foo',
						'bar' => 'baz',
					],
				],
			],
			$this->printer->printType($type),
		);

		$this->printer->requiredValuePlaceholder = '__REQUIRED__';

		self::assertSame(
			[
				'structure' => [
					'string' => 'foo',
					'nullableString' => null,
					'arrayOfMixed' => [
						0 => 'foo',
						'bar' => 'baz',
					],
				],
				'structureOrArray' => '__REQUIRED__',
				'anotherStructureOrArray' => '__REQUIRED__',
				'manyStructures' => '__REQUIRED__',
			],
			$this->printer->printType($type),
		);
	}

}
