<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Types;

use Closure;
use Orisai\ObjectMapper\Exception\WithTypeAndValue;

class ArrayShapeType implements Type
{

	private bool $isInvalid = false;

	/** @var array<int|string, Type|Closure(): Type> */
	private array $fields = [];

	/** @var array<int|string, WithTypeAndValue> */
	private array $invalidFields = [];

	/** @var list<WithTypeAndValue> */
	private array $errors = [];

	/**
	 * @param int|string           $field
	 * @param Type|Closure(): Type $type
	 */
	public function addField($field, $type): void
	{
		$this->fields[$field] = $type;
	}

	/**
	 * @param int|string $field
	 */
	public function overwriteInvalidField($field, WithTypeAndValue $typeAndValue): void
	{
		$this->fields[$field] = $typeAndValue->getType();
		$this->invalidFields[$field] = $typeAndValue;
	}

	/**
	 * @return array<int|string, Type>
	 */
	public function getFields(): array
	{
		$fields = [];
		foreach ($this->fields as $field => $type) {
			if ($type instanceof Closure) {
				$type = $type();
			}

			$fields[$field] = $type;
		}

		return $fields;
	}

	/**
	 * @return array<int|string, WithTypeAndValue>
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
	 * @return list<WithTypeAndValue>
	 */
	public function getErrors(): array
	{
		return $this->errors;
	}

}
