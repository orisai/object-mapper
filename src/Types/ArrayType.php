<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Types;

use Orisai\Exceptions\Logic\InvalidArgument;
use Orisai\ObjectMapper\Exception\WithTypeAndValue;

final class ArrayType extends MultiValueType
{

	private ?Type $keyType;

	/** @var array<int|string, KeyValueErrorPair> */
	private array $invalidPairs = [];

	public function __construct(?Type $keyType, Type $itemType)
	{
		parent::__construct($itemType);
		$this->keyType = $keyType;
	}

	public function getKeyType(): ?Type
	{
		return $this->keyType;
	}

	/**
	 * @param int|string $key
	 */
	public function addInvalidPair($key, ?WithTypeAndValue $keyTypeAndValue, ?WithTypeAndValue $itemTypeAndValue): void
	{
		if ($keyTypeAndValue === null && $itemTypeAndValue === null) {
			throw InvalidArgument::create()
				->withMessage('At least one of key type and item type of invalid pair should not be null');
		}

		$this->invalidPairs[$key] = new KeyValueErrorPair($keyTypeAndValue, $itemTypeAndValue);
	}

	/**
	 * @param int|string $key
	 */
	public function addInvalidKey($key, ?WithTypeAndValue $keyTypeAndValue): void
	{
		$previous = $this->invalidPairs[$key] ?? null;
		$this->invalidPairs[$key] = $previous !== null
			? new KeyValueErrorPair($keyTypeAndValue, $previous->getValue())
			: new KeyValueErrorPair($keyTypeAndValue, null);
	}

	/**
	 * @param int|string $key
	 */
	public function addInvalidValue($key, ?WithTypeAndValue $itemTypeAndValue): void
	{
		$previous = $this->invalidPairs[$key] ?? null;
		$this->invalidPairs[$key] = $previous !== null
			? new KeyValueErrorPair($previous->getKey(), $itemTypeAndValue)
			: new KeyValueErrorPair(null, $itemTypeAndValue);
	}

	public function hasInvalidPairs(): bool
	{
		return $this->invalidPairs !== [];
	}

	/**
	 * @return array<int|string, KeyValueErrorPair>
	 */
	public function getInvalidPairs(): array
	{
		return $this->invalidPairs;
	}

}
