<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Types;

use Orisai\Exceptions\Logic\InvalidArgument;

final class ArrayType extends MultiValueType
{

	private ?Type $keyType;

	/**
	 * @var array<array<Type|null>>
	 * @phpstan-var array<array{?Type, ?Type}>
	 */
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
	 * @param string|int $key
	 */
	public function addInvalidPair($key, ?Type $keyType, ?Type $itemType): void
	{
		if ($keyType === null && $itemType === null) {
			throw InvalidArgument::create()
				->withMessage('At least one of key type and item type of invalid pair should not be null');
		}

		$this->invalidPairs[$key] = [$keyType, $itemType];
	}

	public function hasInvalidPairs(): bool
	{
		return $this->invalidPairs !== [];
	}

	/**
	 * @return array<array<Type|null>>
	 * @phpstan-return array<array{?Type, ?Type}>
	 */
	public function getInvalidPairs(): array
	{
		return $this->invalidPairs;
	}

}
