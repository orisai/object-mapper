<?php declare(strict_types = 1);

namespace Tests\Orisai\ObjectMapper\Toolkit;

use Orisai\ObjectMapper\Context\FieldContext;
use Orisai\ObjectMapper\Context\RuleArgsContext;
use Orisai\ObjectMapper\Context\TypeContext;
use Orisai\ObjectMapper\Meta\DefaultValueMeta;
use Orisai\ObjectMapper\Meta\MetaLoader;
use Orisai\ObjectMapper\Processing\DefaultProcessor;
use Orisai\ObjectMapper\Processing\Options;
use Orisai\ObjectMapper\Rules\DefaultRuleManager;
use Orisai\ObjectMapper\Tester\ObjectMapperTester;
use Orisai\ObjectMapper\Tester\TesterDependencies;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionProperty;
use Tests\Orisai\ObjectMapper\Doubles\NoDefaultsVO;

abstract class ProcessingTestCase extends TestCase
{

	private TesterDependencies $dependencies;

	protected MetaLoader $metaLoader;

	protected DefaultRuleManager $ruleManager;

	protected DefaultProcessor $processor;

	protected function setUp(): void
	{
		$tester = new ObjectMapperTester();
		$this->dependencies = $deps = $tester->buildDependencies();

		$this->ruleManager = $deps->ruleManager;
		$this->metaLoader = $deps->metaLoader;
		$this->processor = $deps->processor;
	}

	protected function ruleArgsContext(?ReflectionProperty $property = null): RuleArgsContext
	{
		if ($property === null) {
			$class = new ReflectionClass(NoDefaultsVO::class);
			$property = $class->getProperty('string');
		}

		return $this->dependencies->createRuleArgsContext($property);
	}

	protected function createTypeContext(): TypeContext
	{
		return new TypeContext($this->metaLoader, $this->ruleManager);
	}

	protected function fieldContext(
		?DefaultValueMeta $defaultValueMeta = null,
		?Options $options = null,
		bool $initializeObjects = false
	): FieldContext
	{
		return $this->dependencies->createFieldContext($defaultValueMeta, $options, $initializeObjects);
	}

}
