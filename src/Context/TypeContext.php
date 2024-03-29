<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Context;

use Orisai\ObjectMapper\Meta\Meta;
use Orisai\ObjectMapper\Meta\MetaLoader;
use Orisai\ObjectMapper\Rules\Rule;
use Orisai\ObjectMapper\Rules\RuleManager;
use Orisai\ObjectMapper\ValueObject;

class TypeContext
{

	private MetaLoader $metaLoader;

	private RuleManager $ruleManager;

	public function __construct(MetaLoader $metaLoader, RuleManager $ruleManager)
	{
		$this->metaLoader = $metaLoader;
		$this->ruleManager = $ruleManager;
	}

	/**
	 * @param class-string<ValueObject> $class
	 */
	public function getMeta(string $class): Meta
	{
		return $this->metaLoader->load($class);
	}

	/**
	 * @template T
	 * @param class-string<T> $rule
	 * @return T
	 */
	public function getRule(string $rule): Rule
	{
		return $this->ruleManager->getRule($rule);
	}

}
