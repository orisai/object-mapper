<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Context;

use Orisai\ObjectMapper\Meta\MetaLoader;
use Orisai\ObjectMapper\Meta\MetaResolver;
use Orisai\ObjectMapper\Rules\RuleManager;
use Orisai\ObjectMapper\ValueObject;
use ReflectionClass;
use ReflectionProperty;
use function assert;

class RuleArgsContext extends ArgsContext
{

	private RuleManager $ruleManager;
	private MetaLoader $metaLoader;

	/**
	 * @phpstan-param ReflectionClass<ValueObject> $class
	 */
	public function __construct(
		ReflectionClass $class,
		ReflectionProperty $property,
		RuleManager $ruleManager,
		MetaLoader $metaLoader,
		MetaResolver $metaResolver
	)
	{
		parent::__construct($class, $property, $metaResolver);
		$this->ruleManager = $ruleManager;
		$this->metaLoader = $metaLoader;
	}

	public function getRuleManager(): RuleManager
	{
		return $this->ruleManager;
	}

	public function getMetaLoader(): MetaLoader
	{
		return $this->metaLoader;
	}

	public function getProperty(): ReflectionProperty
	{
		$property = parent::getProperty();
		assert($property !== null);

		return $property;
	}

}
