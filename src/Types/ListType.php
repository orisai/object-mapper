<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Types;

final class ListType extends MultiValueType
{

	private bool $areKeysInvalid = false;

	/** @var array<Type> */
	private array $invalidItems = [];

	public function markKeysInvalid(): void
	{
		$this->areKeysInvalid = true;
	}

	public function areKeysInvalid(): bool
	{
		return $this->areKeysInvalid;
	}

	/**
	 * @param string|int $key
	 */
	public function addInvalidItem($key, Type $itemType): void
	{
		$this->invalidItems[$key] = $itemType;
	}

	public function hasInvalidItems(): bool
	{
		return $this->invalidItems !== [];
	}

	/**
	 * @return array<Type>
	 */
	public function getInvalidItems(): array
	{
		return $this->invalidItems;
	}

}
