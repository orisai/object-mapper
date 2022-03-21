<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Types;

use Orisai\ObjectMapper\Exceptions\WithTypeAndValue;

final class ListType extends MultiValueType
{

	private bool $areKeysInvalid = false;

	/** @var array<WithTypeAndValue> */
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
	public function addInvalidItem($key, WithTypeAndValue $typeAndValue): void
	{
		$this->invalidItems[$key] = $typeAndValue;
	}

	public function hasInvalidItems(): bool
	{
		return $this->invalidItems !== [];
	}

	/**
	 * @return array<WithTypeAndValue>
	 */
	public function getInvalidItems(): array
	{
		return $this->invalidItems;
	}

}
