<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Meta;

use Orisai\ObjectMapper\Processing\ObjectCreator;
use Orisai\ObjectMapper\Rules\RuleManager;

final class MetaResolverFactory
{

	private RuleManager $ruleManager;

	private ObjectCreator $objectCreator;

	public function __construct(RuleManager $ruleManager, ObjectCreator $objectCreator)
	{
		$this->ruleManager = $ruleManager;
		$this->objectCreator = $objectCreator;
	}

	public function create(MetaLoader $loader): MetaResolver
	{
		return new MetaResolver($loader, $this->ruleManager, $this->objectCreator);
	}

}
