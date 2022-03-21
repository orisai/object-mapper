<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Types;

use Orisai\ObjectMapper\Exceptions\WithTypeAndValue;
use Orisai\ObjectMapper\MappedObject;

final class StructureType implements Type
{

	private bool $isInvalid = false;

	/** @var class-string<MappedObject> */
	private string $class;

	/** @var array<Type> */
	private array $fields = [];

	/** @var array<WithTypeAndValue> */
	private array $invalidFields = [];

	/** @var array<WithTypeAndValue> */
	private array $errors = [];

	/**
	 * @param class-string<MappedObject> $class
	 */
	public function __construct(string $class)
	{
		$this->class = $class;
	}

	/**
	 * @return class-string<MappedObject>
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
	public function overwriteInvalidField($field, WithTypeAndValue $typeAndValue): void
	{
		$this->fields[$field] = $typeAndValue->getInvalidType();
		$this->invalidFields[$field] = $typeAndValue;
	}

	/**
	 * @return array<Type>
	 */
	public function getFields(): array
	{
		return $this->fields;
	}

	/**
	 * @return array<WithTypeAndValue>
	 */
	public function getInvalidFields(): array
	{
		return $this->invalidFields;
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
		return isset($this->invalidFields[$field]);
	}

	public function addError(WithTypeAndValue $error): void
	{
		$this->errors[] = $error;
	}

	public function hasErrors(): bool
	{
		return $this->errors !== [];
	}

	/**
	 * @return array<WithTypeAndValue>
	 */
	public function getErrors(): array
	{
		return $this->errors;
	}

}
