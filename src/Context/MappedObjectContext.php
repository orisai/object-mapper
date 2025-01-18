<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Context;

use Closure;
use Orisai\ObjectMapper\Meta\MetaLoader;
use Orisai\ObjectMapper\Processing\Options;
use Orisai\ObjectMapper\Processing\Processor;
use Orisai\ObjectMapper\Rules\RuleManager;
use Orisai\ObjectMapper\Types\MappedObjectType;

final class MappedObjectContext extends BaseFieldContext
{

	/** @var Closure(): MappedObjectType */
	private Closure $typeCreator;

	private ?MappedObjectType $type = null;

	/**
	 * @param Closure(): MappedObjectType $typeCreator
	 */
	public function __construct(
		MetaLoader $metaLoader,
		RuleManager $ruleManager,
		Processor $processor,
		Options $options,
		Closure $typeCreator,
		bool $initializeObjects
	)
	{
		parent::__construct($metaLoader, $ruleManager, $processor, $options, $initializeObjects);
		$this->typeCreator = $typeCreator;
	}

	public function getType(): MappedObjectType
	{
		if ($this->type !== null) {
			return $this->type;
		}

		$type = ($this->typeCreator)();
		unset($this->typeCreator);

		return $this->type = $type;
	}

	public function getTypeIfInitialized(): ?MappedObjectType
	{
		return $this->type;
	}

}
