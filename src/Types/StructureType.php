<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Types;

use Orisai\ObjectMapper\ValueObject;

final class StructureType implements Type
{

	private bool $isInvalid = false;

	/** @var class-string<ValueObject> */
	private string $class;

	/** @var array<Type> */
	private array $fields = [];

	/** @var array<bool> */
	private array $invalidFields = [];

	/** @var array<Type> */
	private array $errors = [];

	/**
	 * @param class-string<ValueObject> $class
	 */
	public function __construct(string $class)
	{
		$this->class = $class;
	}

	/**
	 * @return class-string<ValueObject>
	 */
	public function getClass(): string
	{
		return $this->class;
	}

	/**
	 * @param int|string $field
	 */
	public function addField($field, Type $type): void
	{
		$this->fields[$field] = $type;
	}

	/**
	 * @param int|string $field
	 */
	public function overwriteInvalidField($field, Type $type): void
	{
		$this->fields[$field] = $type;
		$this->invalidFields[$field] = true;
	}

	/**
	 * @return array<Type>
	 */
	public function getFields(): array
	{
		return $this->fields;
	}

	public function markInvalid(): void
	{
		$this->isInvalid = true;
	}

	public function isInvalid(): bool
	{
		return $this->isInvalid;
	}

	public function hasInvalidFields(): bool
	{
		return $this->invalidFields !== [];
	}

	/**
	 * @param int|string $field
	 */
	public function isFieldInvalid($field): bool
	{
		return $this->invalidFields[$field] ?? false;
	}

	public function addError(Type $type): void
	{
		$this->errors[] = $type;
	}

	public function hasErrors(): bool
	{
		return $this->errors !== [];
	}

	/**
	 * @return array<Type>
	 */
	public function getErrors(): array
	{
		return $this->errors;
	}

}
