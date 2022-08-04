<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Types;

use Orisai\Exceptions\Logic\InvalidArgument;
use Orisai\ObjectMapper\Exception\WithTypeAndValue;

final class ArrayType extends ParametrizedType
{

	private string $name;

	private ?Type $keyType;

	private Type $itemType;

	private bool $isInvalid = false;

	/** @var array<int|string, KeyValueErrorPair> */
	private array $invalidPairs = [];

	private function __construct(string $name, ?Type $keyType, Type $itemType)
	{
		$this->name = $name;
		$this->keyType = $keyType;
		$this->itemType = $itemType;
	}

	public static function forArray(?Type $keyType, Type $itemType): self
	{
		return new self('array', $keyType, $itemType);
	}

	public static function forList(?Type $keyType, Type $itemType): self
	{
		return new self('list', $keyType, $itemType);
	}

	public function getName(): string
	{
		return $this->name;
	}

	public function getKeyType(): ?Type
	{
		return $this->keyType;
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
