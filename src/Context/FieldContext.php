<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Context;

use Orisai\ObjectMapper\Meta\DefaultValueMeta;
use Orisai\ObjectMapper\Meta\MetaLoader;
use Orisai\ObjectMapper\Options;
use Orisai\ObjectMapper\Processing\Processor;
use Orisai\ObjectMapper\Rules\RuleManager;
use Orisai\ObjectMapper\Types\Type;

class FieldContext extends BaseFieldContext
{

	private Type $type;
	private DefaultValueMeta $default;

	/** @var int|string */
	private $fieldName;
	private string $propertyName;

	/**
	 * @param int|string $fieldName
	 */
	public function __construct(
		MetaLoader $metaLoader,
		RuleManager $ruleManager,
		Processor $processor,
		Options $options,
		Type $type,
		DefaultValueMeta $default,
		bool $initializeObjects,
		$fieldName,
		string $propertyName
	)
	{
		parent::__construct($metaLoader, $ruleManager, $processor, $options, $initializeObjects);
		$this->type = $type;
		$this->default = $default;
		$this->fieldName = $fieldName;
		$this->propertyName = $propertyName;
	}

	public function getType(): Type
	{
		return $this->type;
	}

	public function hasDefaultValue(): bool
	{
		return $this->default->hasValue();
	}

	/**
	 * @return mixed
	 */
	public function getDefaultValue()
	{
		return $this->default->getValue();
	}

	/**
	 * @return int|string
	 */
	public function getFieldName()
	{
		return $this->fieldName;
	}

	public function getPropertyName(): string
	{
		return $this->propertyName;
	}

}
