<?php declare(strict_types = 1);

namespace Tests\Orisai\ObjectMapper\Unit\Printers;

use Orisai\ObjectMapper\Attributes\AnnotationsMetaSource;
use Orisai\ObjectMapper\Context\TypeContext;
use Orisai\ObjectMapper\MappedObject;
use Orisai\ObjectMapper\Meta\ArrayMetaCache;
use Orisai\ObjectMapper\Meta\DefaultMetaResolverFactory;
use Orisai\ObjectMapper\Meta\DefaultMetaSourceManager;
use Orisai\ObjectMapper\Meta\MetaLoader;
use Orisai\ObjectMapper\Printers\DefaultValuesArrayPrinter;
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

	private DefaultValuesArrayPrinter $formatter;

	private RuleManager $ruleManager;

	private MetaLoader $metaLoader;

	protected function setUp(): void
	{
		$this->ruleManager = new DefaultRuleManager();

		$sourceManager = new DefaultMetaSourceManager();
		$sourceManager->addSource(new AnnotationsMetaSource());

		$cache = new ArrayMetaCache();
		$resolverFactory = new DefaultMetaResolverFactory($this->ruleManager);
		$this->metaLoader = new MetaLoader($cache, $sourceManager, $resolverFactory);
		$this->formatter = new DefaultValuesArrayPrinter($this->metaLoader);
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
				'nullableString' => null,
				'untypedNullableString' => null,
				'untypedNull' => null,
				'arrayOfMixed' => [
					0 => 'foo',
					'bar' => 'baz',
				],
			],
			$this->formatter->printType($type),
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
					'untypedNullableString' => null,
					'untypedNull' => null,
					'arrayOfMixed' => [
						0 => 'foo',
						'bar' => 'baz',
					],
				],
			],
			$this->formatter->printType($type),
		);

		$this->formatter->requiredValuePlaceholder = '__REQUIRED__';

		self::assertSame(
			[
				'string' => '__REQUIRED__',
				'nullableString' => '__REQUIRED__',
				'untypedString' => '__REQUIRED__',
				'arrayOfMixed' => '__REQUIRED__',
				'structure' => [
					'string' => 'foo',
					'nullableString' => null,
					'untypedNullableString' => null,
					'untypedNull' => null,
					'arrayOfMixed' => [
						0 => 'foo',
						'bar' => 'baz',
					],
				],
				'manyStructures' => '__REQUIRED__',
			],
			$this->formatter->printType($type),
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
					'untypedNullableString' => null,
					'untypedNull' => null,
					'arrayOfMixed' => [
						0 => 'foo',
						'bar' => 'baz',
					],
				],
			],
			$this->formatter->printType($type),
		);

		$this->formatter->requiredValuePlaceholder = '__REQUIRED__';

		self::assertSame(
			[
				'structure' => [
					'string' => 'foo',
					'nullableString' => null,
					'untypedNullableString' => null,
					'untypedNull' => null,
					'arrayOfMixed' => [
						0 => 'foo',
						'bar' => 'baz',
					],
				],
				'structureOrArray' => '__REQUIRED__',
				'anotherStructureOrArray' => '__REQUIRED__',
				'manyStructures' => '__REQUIRED__',
			],
			$this->formatter->printType($type),
		);
	}

}
