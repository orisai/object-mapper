<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Tester;

use Orisai\ObjectMapper\Attributes\AttributesMetaSource;
use Orisai\ObjectMapper\Meta\ArrayMetaCache;
use Orisai\ObjectMapper\Meta\DefaultMetaResolverFactory;
use Orisai\ObjectMapper\Meta\DefaultMetaSourceManager;
use Orisai\ObjectMapper\Meta\MetaLoader;
use Orisai\ObjectMapper\Processing\DefaultObjectCreator;
use Orisai\ObjectMapper\Processing\DefaultProcessor;
use Orisai\ObjectMapper\Rules\DefaultRuleManager;

final class ObjectMapperTester
{

	public function buildDependencies(): TesterDependencies
	{
		$ruleManager = new DefaultRuleManager();

		$sourceManager = new DefaultMetaSourceManager();
		$sourceManager->addSource(new AttributesMetaSource());

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
