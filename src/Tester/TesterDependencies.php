<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Tester;

use Orisai\ObjectMapper\Meta\Context\MetaContext;
use Orisai\ObjectMapper\Meta\Context\MetaFieldContext;
use Orisai\ObjectMapper\Meta\MetaLoader;
use Orisai\ObjectMapper\Meta\MetaResolver;
use Orisai\ObjectMapper\Meta\Shared\DefaultValueMeta;
use Orisai\ObjectMapper\Processing\Context\DynamicContext;
use Orisai\ObjectMapper\Processing\Context\PropertyContext;
use Orisai\ObjectMapper\Processing\Context\ServicesContext;
use Orisai\ObjectMapper\Processing\DefaultDependencyInjectorManager;
use Orisai\ObjectMapper\Processing\Options;
use Orisai\ObjectMapper\Processing\Processor;
use Orisai\ObjectMapper\Rules\DefaultRuleManager;
use ReflectionProperty;

final class TesterDependencies
{

	public MetaLoader $metaLoader;

	public MetaResolver $metaResolver;

	public DefaultRuleManager $ruleManager;

	public Processor $processor;

	public DefaultDependencyInjectorManager $dependencyInjectorManager;

	public ServicesContext $servicesContext;

	/**
	 * @internal
	 * @see ObjectMapperTester::buildDependencies()
	 */
	public function __construct(
		MetaLoader $metaLoader,
		MetaResolver $metaResolver,
		DefaultRuleManager $ruleManager,
		Processor $processor,
		DefaultDependencyInjectorManager $dependencyInjectorManager
	)
	{
		$this->metaLoader = $metaLoader;
		$this->metaResolver = $metaResolver;
		$this->ruleManager = $ruleManager;
		$this->processor = $processor;
		$this->dependencyInjectorManager = $dependencyInjectorManager;
		$this->servicesContext = new ServicesContext($metaLoader, $ruleManager, $processor);
	}

	public function createArgsFieldContext(?DefaultValueMeta $default = null): MetaFieldContext
	{
		return new MetaFieldContext(
			$this->metaLoader,
			$this->metaResolver,
			$default ?? DefaultValueMeta::fromNothing(),
		);
	}

	public function createArgsContext(): MetaContext
	{
		return new MetaContext($this->metaLoader, $this->metaResolver);
	}

	public function createPropertyContext(?DefaultValueMeta $default = null): PropertyContext
	{
		return new PropertyContext(
			$default ?? DefaultValueMeta::fromNothing(),
			new ReflectionProperty(self::class, 'processor'),
			'test',
		);
	}

	public function createDynamicContext(?Options $options = null, bool $initializeObjects = false): DynamicContext
	{
		return new DynamicContext($options ?? new Options(), $initializeObjects);
	}

}
