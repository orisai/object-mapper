<?php declare(strict_types = 1);

namespace Tests\Orisai\ObjectMapper\Unit\Formatting;

use Orisai\ObjectMapper\Annotation\AnnotationMetaSource;
use Orisai\ObjectMapper\Context\TypeContext;
use Orisai\ObjectMapper\Formatting\ArrayDefaultValuesFormatter;
use Orisai\ObjectMapper\Meta\DefaultMetaResolverFactory;
use Orisai\ObjectMapper\Meta\DefaultMetaSourceManager;
use Orisai\ObjectMapper\Meta\MetaLoader;
use Orisai\ObjectMapper\Rules\DefaultRuleManager;
use Orisai\ObjectMapper\Rules\RuleManager;
use Orisai\ObjectMapper\Rules\StructureArgs;
use Orisai\ObjectMapper\Rules\StructureRule;
use Orisai\ObjectMapper\Types\StructureType;
use Orisai\ObjectMapper\ValueObject;
use PHPUnit\Framework\TestCase;
use Tests\Orisai\ObjectMapper\Doubles\DefaultsVO;
use Tests\Orisai\ObjectMapper\Doubles\NoDefaultsVO;
use Tests\Orisai\ObjectMapper\Doubles\StructuresVO;
use Tests\Orisai\ObjectMapper\Doubles\TestMetaCache;

final class ArrayDefaultValuesFormatterTest extends TestCase
{

	private ArrayDefaultValuesFormatter $formatter;

	private RuleManager $ruleManager;

	private MetaLoader $metaLoader;

	protected function setUp(): void
	{
		$this->ruleManager = new DefaultRuleManager();

		$sourceManager = new DefaultMetaSourceManager();
		$sourceManager->addSource(new AnnotationMetaSource());

		$cache = new TestMetaCache();
		$resolverFactory = new DefaultMetaResolverFactory($this->ruleManager);
		$this->metaLoader = new MetaLoader($cache, $sourceManager, $this->ruleManager, $resolverFactory);
		$this->formatter = new ArrayDefaultValuesFormatter($this->metaLoader);
	}

	/**
	 * @param class-string<ValueObject> $class
	 */
	private function createType(string $class): StructureType
	{
		$type = $this->ruleManager->getRule(StructureRule::class)->createType(
			StructureArgs::fromClass($class),
			new TypeContext($this->metaLoader, $this->ruleManager),
		);
		self::assertInstanceOf(StructureType::class, $type);

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
			$this->formatter->formatType($type),
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
			$this->formatter->formatType($type),
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
			$this->formatter->formatType($type),
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
			$this->formatter->formatType($type),
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
			$this->formatter->formatType($type),
		);
	}

}
