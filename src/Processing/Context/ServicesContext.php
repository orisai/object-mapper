<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Processing\Context;

use Orisai\ObjectMapper\MappedObject;
use Orisai\ObjectMapper\Meta\MetaLoader;
use Orisai\ObjectMapper\Meta\Runtime\RuntimeMeta;
use Orisai\ObjectMapper\Processing\Processor;
use Orisai\ObjectMapper\Rules\Rule;
use Orisai\ObjectMapper\Rules\RuleManager;

/**
 * @readonly
 */
final class ServicesContext
{

	private MetaLoader $metaLoader;

	private RuleManager $ruleManager;

	private Processor $processor;

	public function __construct(
		MetaLoader $metaLoader,
		RuleManager $ruleManager,
		Processor $processor
	)
	{
		$this->metaLoader = $metaLoader;
		$this->ruleManager = $ruleManager;
		$this->processor = $processor;
	}

	/**
	 * @param class-string<MappedObject> $class
	 */
	public function getMeta(string $class): RuntimeMeta
	{
		return $this->metaLoader->load($class);
	}

	/**
	 * @template T of Rule
	 * @param class-string<T> $rule
	 * @return T
	 */
	public function getRule(string $rule): Rule
	{
		return $this->ruleManager->getRule($rule);
	}

	public function getProcessor(): Processor
	{
		return $this->processor;
	}

}
