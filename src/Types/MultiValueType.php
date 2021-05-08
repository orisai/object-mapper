<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Types;

abstract class MultiValueType extends ParametrizedType
{

	protected Type $itemType;

	protected bool $isInvalid = false;

	public function __construct(Type $itemType)
	{
		$this->itemType = $itemType;
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
