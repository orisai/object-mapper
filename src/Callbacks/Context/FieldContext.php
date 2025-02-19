<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Callbacks\Context;

use Orisai\ObjectMapper\MappedObject;
use Orisai\ObjectMapper\Processing\Context\DynamicContext;
use Orisai\ObjectMapper\Processing\Context\ProcessorCallContext;
use Orisai\ObjectMapper\Processing\Context\PropertyContext;
use Orisai\ObjectMapper\Processing\Context\ServicesContext;
use Orisai\ObjectMapper\Types\Type;

final class FieldContext extends CallbackBaseContext
{

	private PropertyContext $property;

	/** @var ProcessorCallContext<MappedObject> */
	private ProcessorCallContext $call;

	/**
	 * @param ProcessorCallContext<MappedObject> $call
	 */
	public function __construct(
		ServicesContext $services,
		DynamicContext $dynamic,
		PropertyContext $property,
		ProcessorCallContext $call
	)
	{
		parent::__construct($services, $dynamic);
		$this->property = $property;
		$this->call = $call;
	}

	public function getType(): Type
	{
		return $this->call->getType()->getField($this->property->getFieldName());
	}

	public function getPropertyName(): string
	{
		return $this->property->getPropertyName();
	}

	/**
	 * @return int|string
	 */
	public function getFieldName()
	{
		return $this->property->getFieldName();
	}

}
