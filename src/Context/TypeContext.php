<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Context;

use Orisai\ObjectMapper\MappedObject;
use Orisai\ObjectMapper\Meta\MetaLoader;
use Orisai\ObjectMapper\Meta\Runtime\RuntimeMeta;
use Orisai\ObjectMapper\Processing\Options;
use Orisai\ObjectMapper\Rules\Rule;
use Orisai\ObjectMapper\Rules\RuleManager;
use function array_keys;

class TypeContext
{

	private MetaLoader $metaLoader;

	private RuleManager $ruleManager;

	private Options $options;

	/** @var array<class-string<MappedObject>, true> */
	private array $processedClasses = [];

	public function __construct(MetaLoader $metaLoader, RuleManager $ruleManager, Options $options)
	{
		$this->metaLoader = $metaLoader;
		$this->ruleManager = $ruleManager;
		$this->options = $options;
	}

	public function getOptions(): Options
	{
		return $this->options;
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

	/**
	 * @param class-string<MappedObject> $class
	 * @return static
	 */
	public function withProcessedClass(string $class): self
	{
		$self = $this->createClone();
		$self->processedClasses[$class] = true;

		return $self;
	}

	/**
	 * @return list<class-string<MappedObject>>
	 */
	public function getProcessedClasses(): array
	{
		return array_keys($this->processedClasses);
	}

	/**
	 * @return static
	 */
	public function createClone(): self
	{
		$clone = clone $this;
		$clone->options = $this->options->createClone();

		return $clone;
	}

}
