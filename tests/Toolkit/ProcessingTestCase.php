<?php declare(strict_types = 1);

namespace Tests\Orisai\ObjectMapper\Toolkit;

use Orisai\ObjectMapper\Attributes\AnnotationsMetaSource;
use Orisai\ObjectMapper\Creation\DefaultObjectCreator;
use Orisai\ObjectMapper\Meta\DefaultMetaResolverFactory;
use Orisai\ObjectMapper\Meta\DefaultMetaSourceManager;
use Orisai\ObjectMapper\Meta\MetaLoader;
use Orisai\ObjectMapper\Meta\MetaResolver;
use Orisai\ObjectMapper\Processing\DefaultProcessor;
use Orisai\ObjectMapper\Processing\Processor;
use Orisai\ObjectMapper\Rules\DefaultRuleManager;
use PHPUnit\Framework\TestCase;
use Tests\Orisai\ObjectMapper\Doubles\TestMetaCache;

abstract class ProcessingTestCase extends TestCase
{

	protected MetaLoader $metaLoader;

	protected MetaResolver $metaResolver;

	protected DefaultRuleManager $ruleManager;

	protected Processor $processor;

	protected function setUp(): void
	{
		$this->ruleManager = new DefaultRuleManager();

		$sourceManager = new DefaultMetaSourceManager();
		$sourceManager->addSource(new AnnotationsMetaSource());

		$cache = new TestMetaCache();
		$resolverFactory = new DefaultMetaResolverFactory($this->ruleManager);
		$this->metaLoader = new MetaLoader($cache, $sourceManager, $this->ruleManager, $resolverFactory);
		$this->metaResolver = $resolverFactory->create($this->metaLoader);

		$this->processor = new DefaultProcessor($this->metaLoader, $this->ruleManager, new DefaultObjectCreator());
	}

}
