<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Printers;

use Orisai\ObjectMapper\Types\EnumType;
use Orisai\ObjectMapper\Types\MessageType;
use Orisai\ObjectMapper\Types\TypeParameter;
use function array_key_last;
use function explode;
use function implode;
use const PHP_EOL;

final class TypeToStringConverter
{

	/**
	 * Separator between path nodes
	 */
	public string $pathNodeSeparator = ' > ';

	/**
	 * Separator between path and type
	 */
	public string $pathAndTypeSeparator = ': ';

	/**
	 * Separator between type parameters
	 */
	public string $parameterSeparator = ', ';

	/**
	 * Separator between parameter key and value (in case parameter has any value)
	 */
	public string $parameterKeyValueSeparator = ': ';

	/**
	 * Separator around all items (object fields, invalid array and list keys)
	 */
	public string $aroundItemsSeparator = PHP_EOL;

	/**
	 * Separator between items (object fields, invalid array and list keys)
	 *
	 * @var non-empty-string
	 */
	public string $itemsSeparator = PHP_EOL;

	/**
	 * Indentation between items (object fields, invalid array and list keys)
	 */
	public string $itemsIndentation = "\t";

	public function printMessage(MessageType $type): string
	{
		return $type->getMessage();
	}

	/**
	 * @param array<int|string, TypeParameter> $parameters
	 */
	public function printSimpleValue(string $name, array $parameters): string
	{
		return $name
			. $this->printParameters($parameters);
	}

	public function printEnum(EnumType $type): string
	{
		$inlineValues = '';
		$values = $type->getValues();
		$lastKey = array_key_last($values);

		foreach ($values as $key => $value) {
			$separator = $key === $lastKey ? '' : $this->parameterSeparator;
			$inlineValues .= $this->printRawValue($value, false) . $separator;
		}

		return "enum($inlineValues)";
	}

	/**
	 * @param array<int|string, TypeParameter> $parameters
	 */
	public function printParameters(array $parameters): string
	{
		if ($parameters === []) {
			return '';
		}

		$inlineParameters = '';
		$lastKey = array_key_last($parameters);

		foreach ($parameters as $parameter) {
			$key = $parameter->getKey();
			$separator = $key === $lastKey ? '' : $this->parameterSeparator;
			$inlineParameters .= $parameter->hasValue()
				? ($this->printRawValue($key, false)
					. $this->parameterKeyValueSeparator
					. $this->printRawValue($parameter->getValue(), true)
					. $separator)
				: $this->printRawValue($key, false)
				. $separator;
		}

		return "($inlineParameters)";
	}

	/**
	 * @param mixed $value
	 */
	private function printRawValue($value, bool $includeApostrophe = true): string
	{
		$options = new DumperOptions();
		$options->indentChar = $this->itemsSeparator;
		$options->includeApostrophe = $includeApostrophe;

		return Dumper::dumpValue($value, $options);
	}

	/**
	 * @param array<int|string, string> $subtypes
	 */
	public function printCompound(string $operator, array $subtypes): string
	{
		$lastKey = array_key_last($subtypes);
		$formatted = '';

		foreach ($subtypes as $key => $subtype) {
			$separator = $key === $lastKey ? '' : $operator;
			$formatted .= $subtype . $separator;
		}

		return $formatted;
	}

	/**
	 * @param array<int|string, TypeParameter>                   $parameters
	 * @param array<int|string, array{string|null, string|null}> $invalidPairs
	 */
	public function printArray(
		string $name,
		array $parameters,
		?string $keyType,
		?string $itemType,
		array $invalidPairs = []
	): string
	{
		if ($keyType !== null) {
			$keyValueType = '<'
				. $keyType
				. $this->parameterSeparator
				. ($itemType ?? '')
				. '>';
		} elseif ($itemType !== null) {
			$keyValueType = '<'
				. $itemType
				. '>';
		} else {
			$keyValueType = '';
		}

		return $name
			. $this->printParameters($parameters)
			. $keyValueType
			. $this->printInvalidPairs($invalidPairs);
	}

	/**
	 * @param array<int|string, array{string|null, string|null}> $invalidPairs
	 */
	private function printInvalidPairs(array $invalidPairs): string
	{
		if ($invalidPairs === []) {
			return '';
		}

		$invalidPairsString = '';
		$lastKey = array_key_last($invalidPairs);
		foreach ($invalidPairs as $key => [$pairKeyString, $pairValueString]) {
			$invalidPairString = $this->printRawValue($key, false) . $this->pathAndTypeSeparator;

			if ($pairKeyString !== null) {
				$invalidPairString .= $pairKeyString;
				$invalidPairString .= ' => ';
			}

			if ($pairValueString !== null) {
				$invalidPairString .= $pairValueString;
			} else {
				$invalidPairString .= 'value';
			}

			$invalidPairsString .= $this->printItem(
				$invalidPairString,
				$key === $lastKey,
			);
		}

		return '['
			. $this->aroundItemsSeparator
			. $this->indent($invalidPairsString)
			. $this->aroundItemsSeparator
			. ']';
	}

	/**
	 * @param array<int|string, string> $fields
	 * @param array<int|string, string> $errors
	 */
	public function printShape(array $fields, array $errors = []): string
	{
		if ($fields === [] && $errors === []) {
			return 'shape{}';
		}

		$printedFields = '';
		$lastFieldKey = array_key_last($fields);
		foreach ($fields as $fieldName => $field) {
			$formattedField = $fieldName
				. $this->pathAndTypeSeparator
				. $field;
			$printedFields .= $this->printItem(
				$formattedField,
				$errors === [] && $fieldName === $lastFieldKey,
			);
		}

		$printedErrors = '';
		$lastErrorKey = array_key_last($errors);
		foreach ($errors as $errorKey => $error) {
			$printedErrors .= $this->printItem($error, $errorKey === $lastErrorKey);
		}

		$printedItems = $printedFields . $printedErrors;

		return 'shape{'
			. $this->aroundItemsSeparator
			. ($printedItems !== '' ? $this->indent($printedItems) : '')
			. $this->aroundItemsSeparator
			. '}';
	}

	private function printItem(string $item, bool $isLast): string
	{
		return $item . ($isLast ? '' : $this->itemsSeparator);
	}

	private function indent(string $content): string
	{
		$lines = [];
		foreach (explode($this->itemsSeparator, $content) as $line) {
			$lines[] = $this->itemsIndentation . $line;
		}

		return implode($this->itemsSeparator, $lines);
	}

	/**
	 * @param array<int, string>        $pathNodes
	 * @param array<int|string, string> $fields
	 * @param array<int|string, string> $errors
	 */
	public function printError(array $pathNodes, array $fields, array $errors): string
	{
		$printed = '';
		$rootPath = implode($this->pathNodeSeparator, $pathNodes);

		$lastFieldKey = array_key_last($fields);
		foreach ($fields as $fieldName => $field) {
			$fieldSeparator = $errors === [] && $fieldName === $lastFieldKey
				? ''
				: $this->itemsSeparator;

			$printed .=
				($rootPath !== '' ? $rootPath . $this->pathNodeSeparator : '')
				. $fieldName
				. $this->pathAndTypeSeparator
				. $field
				. $fieldSeparator;
		}

		$lastErrorKey = array_key_last($errors);
		foreach ($errors as $errorKey => $error) {
			$fieldSeparator = $errorKey === $lastErrorKey
				? ''
				: $this->itemsSeparator;

			$printed .=
				($rootPath !== '' ? $rootPath . $this->pathNodeSeparator : '')
				. $error
				. $fieldSeparator;
		}

		return $printed;
	}

}
