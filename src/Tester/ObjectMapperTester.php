<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Tester;

use Orisai\ObjectMapper\Meta\Cache\ArrayMetaCache;
use Orisai\ObjectMapper\Meta\MetaLoader;
use Orisai\ObjectMapper\Meta\MetaResolverFactory;
use Orisai\ObjectMapper\Meta\Source\AnnotationsMetaSource;
use Orisai\ObjectMapper\Meta\Source\AttributesMetaSource;
use Orisai\ObjectMapper\Meta\Source\DefaultMetaSourceManager;
use Orisai\ObjectMapper\Processing\DefaultDependencyInjectorManager;
use Orisai\ObjectMapper\Processing\DefaultProcessor;
use Orisai\ObjectMapper\Processing\ObjectCreator;
use Orisai\ObjectMapper\Rules\DefaultRuleManager;
use Orisai\ReflectionMeta\Reader\AnnotationsMetaReader;
use Orisai\ReflectionMeta\Reader\AttributesMetaReader;

final class ObjectMapperTester
{

	public function buildDependencies(): TesterDependencies
	{
		$ruleManager = new DefaultRuleManager();

		$sourceManager = new DefaultMetaSourceManager();

		if (AnnotationsMetaReader::canBeConstructed()) {
			$sourceManager->addSource(new AnnotationsMetaSource());
		}

		if (AttributesMetaReader::canBeConstructed()) {
			$sourceManager->addSource(new AttributesMetaSource());
		}

		$injector = new DefaultDependencyInjectorManager();
		$objectCreator = new ObjectCreator($injector);
		$cache = new ArrayMetaCache();
		$resolverFactory = new MetaResolverFactory($ruleManager, $objectCreator);
		$metaLoader = new MetaLoader($cache, $sourceManager, $resolverFactory);
		$metaResolver = $resolverFactory->create($metaLoader);

		$processor = new DefaultProcessor(
			$metaLoader,
			$ruleManager,
			$objectCreator,
		);

		return new TesterDependencies(
			$metaLoader,
			$metaResolver,
			$ruleManager,
			$processor,
			$injector,
		);
	}

}
