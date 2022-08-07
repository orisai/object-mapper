<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Context;

use Orisai\ObjectMapper\Meta\MetaLoader;
use Orisai\ObjectMapper\Meta\MetaResolver;
use Orisai\ObjectMapper\Rules\RuleManager;
use ReflectionProperty;

final class RuleArgsContext extends BaseArgsContext
{

	private ReflectionProperty $property;

	private RuleManager $ruleManager;

	private MetaLoader $metaLoader;

	public function __construct(
		ReflectionProperty $property,
		RuleManager $ruleManager,
		MetaLoader $metaLoader,
		MetaResolver $metaResolver
	)
	{
		parent::__construct($property->getDeclaringClass(), $metaResolver);
		$this->property = $property;
		$this->ruleManager = $ruleManager;
		$this->metaLoader = $metaLoader;
	}

	public function getProperty(): ReflectionProperty
	{
		return $this->property;
	}

	public function getRuleManager(): RuleManager
	{
		return $this->ruleManager;
	}

	public function getMetaLoader(): MetaLoader
	{
		return $this->metaLoader;
	}

}
