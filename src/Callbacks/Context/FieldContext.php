<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Callbacks\Context;

use Closure;
use Orisai\ObjectMapper\Processing\Context\DynamicContext;
use Orisai\ObjectMapper\Processing\Context\PropertyContext;
use Orisai\ObjectMapper\Processing\Context\ServicesContext;
use Orisai\ObjectMapper\Types\Type;

final class FieldContext extends CallbackBaseContext
{

	private PropertyContext $property;

	/** @var Closure(): Type */
	private Closure $typeCreator;

	private ?Type $type = null;

	/**
	 * @param Closure(): Type $typeCreator
	 */
	public function __construct(
		ServicesContext $services,
		DynamicContext $dynamic,
		PropertyContext $property,
		Closure $typeCreator
	)
	{
		parent::__construct($services, $dynamic);
		$this->property = $property;
		$this->typeCreator = $typeCreator;
	}

	public function getType(): Type
	{
		if ($this->type !== null) {
			return $this->type;
		}

		$type = ($this->typeCreator)();
		unset($this->typeCreator);

		return $this->type = $type;
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
