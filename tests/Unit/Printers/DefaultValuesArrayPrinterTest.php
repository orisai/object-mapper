<?php declare(strict_types = 1);

namespace Tests\Orisai\ObjectMapper\Unit\Printers;

use Orisai\ObjectMapper\Attributes\AttributesMetaSource;
use Orisai\ObjectMapper\Context\TypeContext;
use Orisai\ObjectMapper\MappedObject;
use Orisai\ObjectMapper\Meta\ArrayMetaCache;
use Orisai\ObjectMapper\Meta\DefaultMetaResolverFactory;
use Orisai\ObjectMapper\Meta\DefaultMetaSourceManager;
use Orisai\ObjectMapper\Meta\MetaLoader;
use Orisai\ObjectMapper\Printers\DefaultValuesArrayPrinter;
use Orisai\ObjectMapper\Processing\DefaultObjectCreator;
use Orisai\ObjectMapper\ReflectionMeta\Collector\AnnotationsCollector;
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
		$sourceManager->addSource(new AttributesMetaSource(new AnnotationsCollector()));

		$objectCreator = new DefaultObjectCreator();
		$cache = new ArrayMetaCache();
		$resolverFactory = new DefaultMetaResolverFactory($this->ruleManager, $objectCreator);
		$this->metaLoader = new MetaLoader($cache, $sourceManager, $resolverFactory);
		$this->printer = new DefaultValuesArrayPrinter($this->metaLoader);
	}

	/**
	 * @param class-string<MappedObject> $class
	 */
	private function createType(string $class): MappedObjectType
	{
		$type = $this->ruleManager->getRule(MappedObjectRule::class)->createType(
			new MappedObjectArgs($class),
			new TypeContext($this->metaLoader, $this->ruleManager),
		);
		self::assertInstanceOf(MappedObjectType::class, $type);

		return $type;
	}

	public function testDefaults(): void
	{
		$type = $this->createType(DefaultsVO::class);

		self::assertSame(
			[
				'string' => 'foo',
				'defaultByAttributeString' => 'attribute default',
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
					'defaultByAttributeString' => 'attribute default',
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
					'defaultByAttributeString' => 'attribute default',
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
					'defaultByAttributeString' => 'attribute default',
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
					'defaultByAttributeString' => 'attribute default',
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
