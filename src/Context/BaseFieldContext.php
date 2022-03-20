<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Context;

use Orisai\ObjectMapper\Meta\MetaLoader;
use Orisai\ObjectMapper\Processing\Options;
use Orisai\ObjectMapper\Processing\Processor;
use Orisai\ObjectMapper\Rules\RuleManager;

abstract class BaseFieldContext extends TypeContext
{

	private Processor $processor;

	private Options $options;

	private bool $initializeObjects;

	public function __construct(
		MetaLoader $metaLoader,
		RuleManager $ruleManager,
		Processor $processor,
		Options $options,
		bool $initializeObjects
	)
	{
		parent::__construct($metaLoader, $ruleManager);
		$this->processor = $processor;
		$this->options = $options;
		$this->initializeObjects = $initializeObjects;
	}

	public function getProcessor(): Processor
	{
		return $this->processor;
	}

	public function getOptions(): Options
	{
		return $this->options;
	}

	public function isInitializeObjects(): bool
	{
		return $this->initializeObjects;
	}

}
