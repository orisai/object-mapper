<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Types;

use Orisai\Exceptions\Logic\InvalidArgument;
use Orisai\Exceptions\Logic\InvalidState;
use Orisai\ObjectMapper\Exception\WithTypeAndValue;
use function array_key_exists;
use function implode;
use function in_array;
use function sprintf;

final class CompoundType implements Type
{

	public const
		OPERATOR_AND = '&',
		OPERATOR_OR = '|';

	private const OPERATORS
		= [
			self::OPERATOR_AND,
			self::OPERATOR_OR,
		];

	private const OPERATORS_HUMAN
		= [
			self::OPERATOR_AND => 'and',
			self::OPERATOR_OR => 'or',
		];

	/** @var array<Type> */
	private array $subtypes = [];

	/** @var array<int|string> */
	private array $skippedSubtypes = [];

	/** @var array<WithTypeAndValue> */
	private array $invalidSubtypes = [];

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
					sprintf(
						'Cannot mark subtype with key %s skipped because it was never set',
						$key,
					),
				);
		}

		if ($this->isSubtypeInvalid($key)) {
			throw InvalidState::create()
				->withMessage(
					sprintf(
						'Cannot mark subtype with key %s skipped because it was already overwritten with invalid subtype',
						$key,
					),
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
					sprintf(
						'Cannot overwrite subtype with key %s with invalid subtype because it was never set',
						$key,
					),
				);
		}

		if ($this->isSubtypeSkipped($key)) {
			throw InvalidState::create()
				->withMessage(
					sprintf(
						'Cannot overwrite subtype with key %s because it is already marked as skipped',
						$key,
					),
				);
		}

		$this->subtypes[$key] = $withTypeAndValue->getInvalidType();
		$this->invalidSubtypes[$key] = $withTypeAndValue;
	}

	/**
	 * @param string|int $key
	 */
	public function isSubtypeInvalid($key): bool
	{
		return isset($this->invalidSubtypes[$key]);
	}

	public function getOperator(bool $human = false): string
	{
		return $human
			? self::OPERATORS_HUMAN[$this->operator]
			: $this->operator;
	}

	/**
	 * @param int|string $key
	 */
	public function isSubtypeValid($key): bool
	{
		return !$this->isSubtypeInvalid($key) && !$this->isSubtypeSkipped($key);
	}

}
