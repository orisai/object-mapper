<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Bridge\NetteDI;

use Nette\DI\CompilerExtension;
use Nette\DI\ContainerBuilder;
use Nette\DI\Definitions\FactoryDefinition;
use Nette\DI\Definitions\Reference;
use Nette\DI\Definitions\ServiceDefinition;
use Nette\PhpGenerator\Literal;
use Nette\Schema\Expect;
use Nette\Schema\Schema;
use OriNette\DI\Definitions\DefinitionsLoader;
use Orisai\ObjectMapper\Attributes\AnnotationsMetaSource;
use Orisai\ObjectMapper\Attributes\AttributesMetaSource;
use Orisai\ObjectMapper\Bridge\NetteCache\NetteMetaCache;
use Orisai\ObjectMapper\Meta\DefaultMetaSourceManager;
use Orisai\ObjectMapper\Meta\MetaCache;
use Orisai\ObjectMapper\Meta\MetaLoader;
use Orisai\ObjectMapper\Meta\MetaResolverFactory;
use Orisai\ObjectMapper\Meta\MetaSourceManager;
use Orisai\ObjectMapper\Processing\DefaultProcessor;
use Orisai\ObjectMapper\Processing\ObjectCreator;
use Orisai\ObjectMapper\Processing\Processor;
use Orisai\ObjectMapper\ReflectionMeta\Collector\AnnotationsCollector;
use Orisai\ObjectMapper\ReflectionMeta\Collector\AttributesCollector;
use Orisai\ObjectMapper\Rules\RuleManager;
use stdClass;
use function assert;
use function is_string;
use function str_replace;

/**
 * @property-read stdClass $config
 */
final class ObjectMapperExtension extends CompilerExtension
{

	public function getConfigSchema(): Schema
	{
		return Expect::structure([
			'debug' => Expect::bool(false),
			'rules' => Expect::arrayOf(
				DefinitionsLoader::schema(),
				Expect::string(),
			),
		]);
	}

	public function loadConfiguration(): void
	{
		parent::loadConfiguration();

		$builder = $this->getContainerBuilder();
		$config = $this->config;
		$loader = new DefinitionsLoader($this->compiler);

		$metaCacheDefinition = $this->registerMetaCache($builder, $config->debug);
		$sourceManagerDefinition = $this->registerMetaSourceManager($builder);
		$ruleManagerDefinition = $this->registerRuleManager($config, $builder, $loader);
		$objectCreatorDefinition = $this->registerObjectCreator($builder);
		$resolverFactoryDefinition = $this->registerMetaResolverFactory(
			$builder,
			$ruleManagerDefinition,
			$objectCreatorDefinition,
		);
		$metaLoaderDefinition = $this->registerMetaLoader(
			$builder,
			$metaCacheDefinition,
			$sourceManagerDefinition,
			$resolverFactoryDefinition,
		);
		$this->registerProcessor(
			$builder,
			$metaLoaderDefinition,
			$ruleManagerDefinition,
			$objectCreatorDefinition,
		);
	}

	private function registerMetaSourceManager(ContainerBuilder $builder): ServiceDefinition
	{
		$definition = $builder->addDefinition($this->prefix('metaSourceManager'))
			->setFactory(DefaultMetaSourceManager::class)
			->setType(MetaSourceManager::class)
			->setAutowired(false);

		$this->registerAnnotationsMetaSource($definition, $builder);
		$this->registerAttributesMetaSource($definition, $builder);

		return $definition;
	}

	private function registerAnnotationsMetaSource(
		ServiceDefinition $sourceManagerDefinition,
		ContainerBuilder $builder
	): void
	{
		if (!AnnotationsCollector::canBeConstructed()) {
			return;
		}

		$sourceManagerDefinition->addSetup('addSource', [
			$builder->addDefinition($this->prefix('metaSource.annotations'))
				->setFactory(AnnotationsMetaSource::class, [
					$builder->addDefinition($this->prefix('metaCollector.annotations'))
						->setFactory(AnnotationsCollector::class)
						->setAutowired(false),
				])
				->setAutowired(false),
		]);
	}

	private function registerAttributesMetaSource(
		ServiceDefinition $sourceManagerDefinition,
		ContainerBuilder $builder
	): void
	{
		if (!AttributesCollector::canBeConstructed()) {
			return;
		}

		$sourceManagerDefinition->addSetup('addSource', [
			$builder->addDefinition($this->prefix('metaSource.attributes'))
				->setFactory(AttributesMetaSource::class, [
					$builder->addDefinition($this->prefix('metaCollector.attributes'))
						->setFactory(AttributesCollector::class)
						->setAutowired(false),
				])
				->setAutowired(false),
		]);
	}

	private function registerMetaCache(ContainerBuilder $builder, bool $debugMode): ServiceDefinition
	{
		return $builder->addDefinition($this->prefix('metaCache'))
			->setFactory(NetteMetaCache::class, [
				'debugMode' => $debugMode,
			])
			->setType(MetaCache::class)
			->setAutowired(false);
	}

	private function registerMetaResolverFactory(
		ContainerBuilder $builder,
		ServiceDefinition $ruleManagerDefinition,
		ServiceDefinition $objectCreatorDefinition
	): FactoryDefinition
	{
		$definition = new FactoryDefinition();
		$definition->setImplement(MetaResolverFactory::class)
			->setAutowired(false);
		$builder->addDefinition($this->prefix('metaResolver.factory'), $definition);

		$resultDefinition = $definition->getResultDefinition();
		$resultDefinition->setArguments([
			'ruleManager' => $ruleManagerDefinition,
			'objectCreator' => $objectCreatorDefinition,
		]);

		return $definition;
	}

	private function registerMetaLoader(
		ContainerBuilder $builder,
		ServiceDefinition $metaCacheDefinition,
		ServiceDefinition $sourceManagerDefinition,
		FactoryDefinition $resolverFactoryDefinition
	): ServiceDefinition
	{
		return $builder->addDefinition($this->prefix('metaLoader'))
			->setFactory(MetaLoader::class, [
				'metaCache' => $metaCacheDefinition,
				'sourceManager' => $sourceManagerDefinition,
				'resolverFactory' => $resolverFactoryDefinition,
			]);
	}

	private function registerRuleManager(
		stdClass $config,
		ContainerBuilder $builder,
		DefinitionsLoader $loader
	): ServiceDefinition
	{
		$ruleManagerDefinition = $builder->addDefinition($this->prefix('ruleManager'))
			->setFactory(LazyRuleManager::class)
			->setType(RuleManager::class)
			->setAutowired(false);

		foreach ($config->rules as $ruleName => $ruleConfig) {
			assert(is_string($ruleName));

			$ruleKey = str_replace('\\', '', $ruleName);
			$ruleDefinition = $loader->loadDefinitionFromConfig(
				$ruleConfig,
				$this->prefix("rule.$ruleKey"),
			);

			$ruleManagerDefinition->addSetup('?->addLazyRule(?, ?)', [
				$ruleManagerDefinition,
				new Literal("\\{$ruleName}::class"),
				$ruleDefinition instanceof Reference
					? $ruleDefinition->getValue()
					: $ruleDefinition->getName(),
			]);
		}

		return $ruleManagerDefinition;
	}

	private function registerObjectCreator(ContainerBuilder $builder): ServiceDefinition
	{
		return $builder->addDefinition($this->prefix('objectCreator'))
			->setFactory(LazyObjectCreator::class)
			->setType(ObjectCreator::class)
			->setAutowired(false);
	}

	private function registerProcessor(
		ContainerBuilder $builder,
		ServiceDefinition $metaLoaderDefinition,
		ServiceDefinition $ruleManagerDefinition,
		ServiceDefinition $objectCreatorDefinition
	): void
	{
		$builder->addDefinition($this->prefix('processor'))
			->setFactory(DefaultProcessor::class, [
				'metaLoader' => $metaLoaderDefinition,
				'ruleManager' => $ruleManagerDefinition,
				'objectCreator' => $objectCreatorDefinition,
			])
			->setType(Processor::class);
	}

}
