<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Types;

use Orisai\Exceptions\Logic\InvalidArgument;
use Orisai\Exceptions\Logic\InvalidState;
use Orisai\ObjectMapper\Exceptions\WithTypeAndValue;
use function array_key_exists;
use function implode;
use function in_array;
use function sprintf;

final class CompoundType implements Type
{

	public const
		OPERATOR_AND = '&',
		OPERATOR_OR = '|';

	private const OPERATORS = [
		self::OPERATOR_AND,
		self::OPERATOR_OR,
	];

	/** @var array<Type> */
	private array $subtypes = [];

	/** @var array<int|string> */
	private array $skippedSubtypes = [];

	/** @var array<WithTypeAndValue> */
	private array $invalidSubtypes = [];

	/** @phpstan-var self::OPERATOR_* */
	private string $operator;

	public function __construct(string $operator)
	{
		if (!in_array($operator, self::OPERATORS, true)) {
			throw InvalidArgument::create()
				->withMessage(
					sprintf(
						'Invalid operator %s, choose one of %s',
						$operator,
						implode(', ', self::OPERATORS),
					),
				);
		}

		$this->operator = $operator;
	}

	/**
	 * @param string|int $key
	 */
	public function addSubtype($key, Type $node): void
	{
		if (array_key_exists($key, $this->subtypes)) {
			throw InvalidState::create()
				->withMessage(
					sprintf(
						'Cannot set subtype with key %s because it was already set',
						$key,
					),
				);
		}

		$this->subtypes[$key] = $node;
	}

	/**
	 * @return array<Type>
	 */
	public function getSubtypes(): array
	{
		return $this->subtypes;
	}

	/** @return  array<WithTypeAndValue> */
	public function getInvalidSubtypes(): array
	{
		return $this->invalidSubtypes;
	}

	/**
	 * @param string|int $key
	 */
	public function setSubtypeSkipped($key): void
	{
		if (!array_key_exists($key, $this->subtypes)) {
			throw InvalidState::create()
				->withMessage(
					"Cannot mark subtype with key $key skipped because it was never set",
				);
		}

		if ($this->isSubtypeInvalid($key)) {
			throw InvalidState::create()
				->withMessage(
					"Cannot mark subtype with key $key skipped because it was already overwritten with invalid subtype",
				);
		}

		$this->skippedSubtypes[] = $key;
	}

	/**
	 * @param string|int $key
	 */
	public function isSubtypeSkipped($key): bool
	{
		return in_array($key, $this->skippedSubtypes, true);
	}

	/**
	 * @param string|int $key
	 */
	public function overwriteInvalidSubtype($key, WithTypeAndValue $withTypeAndValue): void
	{
		if (!array_key_exists($key, $this->subtypes)) {
			throw InvalidState::create()
				->withMessage(
					"Cannot overwrite subtype with key $key with invalid subtype because it was never set",
				);
		}

		if ($this->isSubtypeSkipped($key)) {
			throw InvalidState::create()
				->withMessage(
					"Cannot overwrite subtype with key $key because it is already marked as skipped",
				);
		}

		$this->subtypes[$key] = $withTypeAndValue->getType();
		$this->invalidSubtypes[$key] = $withTypeAndValue;
	}

	/**
	 * @param string|int $key
	 */
	public function isSubtypeInvalid($key): bool
	{
		return isset($this->invalidSubtypes[$key]);
	}

	/**
	 * @phpstan-return self::OPERATOR_*
	 */
	public function getOperator(): string
	{
		return $this->operator;
	}

	/**
	 * @param int|string $key
	 */
	public function isSubtypeValid($key): bool
	{
		return !$this->isSubtypeInvalid($key) && !$this->isSubtypeSkipped($key);
	}

}
