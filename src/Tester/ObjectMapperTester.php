<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Tester;

use Orisai\ObjectMapper\Meta\Cache\ArrayMetaCache;
use Orisai\ObjectMapper\Meta\DefaultMetaResolverFactory;
use Orisai\ObjectMapper\Meta\MetaLoader;
use Orisai\ObjectMapper\Meta\Source\AnnotationsMetaSource;
use Orisai\ObjectMapper\Meta\Source\AttributesMetaSource;
use Orisai\ObjectMapper\Meta\Source\DefaultMetaSourceManager;
use Orisai\ObjectMapper\Processing\DefaultObjectCreator;
use Orisai\ObjectMapper\Processing\DefaultProcessor;
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

		$objectCreator = new DefaultObjectCreator();
		$cache = new ArrayMetaCache();
		$resolverFactory = new DefaultMetaResolverFactory($ruleManager, $objectCreator);
		$metaLoader = new MetaLoader($cache, $sourceManager, $resolverFactory);
		$metaResolver = $resolverFactory->create($metaLoader);

		$processor = new DefaultProcessor(
			$metaLoader,
			$ruleManager,
			new DefaultObjectCreator(),
		);

		return new TesterDependencies(
			$metaLoader,
			$metaResolver,
			$ruleManager,
			$processor,
		);
	}

}
