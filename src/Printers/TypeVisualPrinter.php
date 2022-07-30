<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Printers;

use Orisai\Exceptions\Logic\InvalidArgument;
use Orisai\ObjectMapper\Types\ArrayType;
use Orisai\ObjectMapper\Types\CompoundType;
use Orisai\ObjectMapper\Types\EnumType;
use Orisai\ObjectMapper\Types\ListType;
use Orisai\ObjectMapper\Types\MappedObjectType;
use Orisai\ObjectMapper\Types\MessageType;
use Orisai\ObjectMapper\Types\ParametrizedType;
use Orisai\ObjectMapper\Types\SimpleValueType;
use Orisai\ObjectMapper\Types\Type;
use function array_key_last;
use function explode;
use function get_class;
use function implode;
use function sprintf;
use const PHP_EOL;

class TypeVisualPrinter implements TypePrinter
{

	/**
	 * Separator between path and type
	 */
	public string $pathAndTypeSeparator = ': ';

	/**
	 * Separator between type and it's parameters
	 */
	public string $typeAndParametersSeparator = '';

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

	public function printType(Type $type): string
	{
		return $this->print($type);
	}

	protected function print(Type $type): string
	{
		if ($type instanceof MappedObjectType) {
			return $this->printMappedObjectType($type);
		}

		if ($type instanceof CompoundType) {
			return $this->printCompoundType($type);
		}

		if ($type instanceof ArrayType) {
			return $this->printArrayType($type);
		}

		if ($type instanceof ListType) {
			return $this->printListType($type);
		}

		if ($type instanceof SimpleValueType) {
			return $this->printSimpleValueType($type);
		}

		if ($type instanceof EnumType) {
			return $this->printEnumType($type);
		}

		if ($type instanceof MessageType) {
			return $this->printMessageType($type);
		}

		throw InvalidArgument::create()
			->withMessage(sprintf('Unsupported type %s', get_class($type)));
	}

	protected function printMappedObjectType(MappedObjectType $type): string
	{
		$formatted = '';

		$fields = $this->filterFields($type);
		$lastFieldKey = array_key_last($fields);

		foreach ($fields as $fieldName => $fieldType) {
			$formattedField = sprintf(
				'%s%s%s',
				$fieldName,
				$this->pathAndTypeSeparator,
				$this->print($fieldType),
			);
			$formatted .= $this->printComplexTypeInnerLine($formattedField, $fieldName === $lastFieldKey);
		}

		if ($formatted === '') {
			return "structure$this->typeAndParametersSeparator[]";
		}

		return "structure$this->typeAndParametersSeparator[$this->aroundItemsSeparator{$this->indent($formatted)}$this->aroundItemsSeparator]";
	}

	/**
	 * @return array<Type>
	 */
	protected function filterFields(MappedObjectType $type): array
	{
		return $type->getFields();
	}

	protected function printCompoundType(CompoundType $type): string
	{
		$formatted = '';
		$subtypes = $type->getSubtypes();
		$lastKey = array_key_last($subtypes);

		foreach ($subtypes as $key => $subtype) {
			$separator = $key === $lastKey ? '' : $type->getOperator();
			$formatted .= sprintf('%s%s', $this->print($subtype), $separator);
		}

		return $formatted;
	}

	protected function printArrayType(ArrayType $type): string
	{
		$keyType = $type->getKeyType();
		$itemType = $type->getItemType();

		$parameters = $this->printParameters($type);

		$formatted = sprintf('%s%s', $this->typeAndParametersSeparator, $parameters);
		$formatted .= $keyType !== null
			? sprintf('<%s%s%s>', $this->print($keyType), $this->parameterSeparator, $this->print($itemType))
			: sprintf('<%s>', $this->print($itemType));

		return "array$formatted";
	}

	protected function printListType(ListType $type): string
	{
		$parameters = $this->printParameters($type);

		$formatted = sprintf('%s%s', $this->typeAndParametersSeparator, $parameters);
		$formatted .= sprintf('<%s>', $this->print($type->getItemType()));

		return "list$formatted";
	}

	protected function printSimpleValueType(SimpleValueType $type): string
	{
		return sprintf('%s%s', $type->getName(), $this->printParameters($type));
	}

	protected function printEnumType(EnumType $type): string
	{
		$inlineValues = '';
		$values = $type->getValues();
		$lastKey = array_key_last($values);

		foreach ($values as $key => $value) {
			$separator = $key === $lastKey ? '' : $this->parameterSeparator;
			$inlineValues .= sprintf('%s%s', $this->valueToString($value, false), $separator);
		}

		return sprintf('enum(%s)', $inlineValues);
	}

	protected function printMessageType(MessageType $type): string
	{
		return $type->getMessage();
	}

	protected function printParameters(ParametrizedType $type): string
	{
		$parameters = $type->getParameters();

		if ($parameters === []) {
			return '';
		}

		$inlineParameters = '';
		$lastKey = array_key_last($parameters);

		foreach ($parameters as $parameter) {
			$key = $parameter->getKey();
			$separator = $key === $lastKey ? '' : $this->parameterSeparator;
			$inlineParameters .= $parameter->hasValue()
				? sprintf(
					'%s%s%s%s',
					$this->valueToString($key, false),
					$this->parameterKeyValueSeparator,
					$this->valueToString($parameter->getValue(), true),
					$separator,
				)
				: sprintf('%s%s', $this->valueToString($key, false), $separator);
		}

		return sprintf('%s(%s)', $this->typeAndParametersSeparator, $inlineParameters);
	}

	/**
	 * @param mixed $value
	 */
	protected function valueToString($value, bool $includeApostrophe = true): string
	{
		return Dumper::dumpValue($value, [
			Dumper::OptIncludeApostrophe => $includeApostrophe,
			Dumper::OptLevel => 1,
			Dumper::OptIndentChar => $this->itemsSeparator,
		]);
	}

	protected function printComplexTypeInnerLine(string $inner, bool $isLast): string
	{
		return $inner . ($isLast ? '' : $this->itemsSeparator);
	}

	private function indent(string $content): string
	{
		$lines = [];
		foreach (explode($this->itemsSeparator, $content) as $line) {
			$lines[] = $this->itemsIndentation . $line;
		}

		return implode($this->itemsSeparator, $lines);
	}

}
