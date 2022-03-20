<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Context;

use Orisai\ObjectMapper\Meta\MetaLoader;
use Orisai\ObjectMapper\Processing\Options;
use Orisai\ObjectMapper\Processing\Processor;
use Orisai\ObjectMapper\Rules\RuleManager;
use Orisai\ObjectMapper\Types\StructureType;

class FieldSetContext extends BaseFieldContext
{

	private StructureType $type;

	public function __construct(
		MetaLoader $metaLoader,
		RuleManager $ruleManager,
		Processor $processor,
		Options $options,
		StructureType $type,
		bool $initializeObjects
	)
	{
		parent::__construct($metaLoader, $ruleManager, $processor, $options, $initializeObjects);
		$this->type = $type;
	}

	public function getType(): StructureType
	{
		return $this->type;
	}

}
