<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Types;

abstract class MultiValueType extends ParametrizedType
{

	protected Type $itemType;
	protected bool $isInvalid = false;

	/**
	 * @param array<mixed> $parameters
	 */
	public function __construct(Type $itemType, array $parameters = [])
	{
		$this->itemType = $itemType;
		$this->parameters = $parameters;
	}

	public function getItemType(): Type
	{
		return $this->itemType;
	}

	public function markInvalid(): void
	{
		$this->isInvalid = true;
	}

	public function isInvalid(): bool
	{
		return $this->isInvalid;
	}

}
