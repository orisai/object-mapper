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
use function get_class;
use function sprintf;
use function str_repeat;
use function str_replace;
use const PHP_EOL;

class TypeVisualPrinter implements TypePrinter
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
	 */
	public string $itemsSeparator = PHP_EOL;

	/**
	 * Indentation between items (object fields, invalid array and list keys)
	 */
	public string $itemsIndentation = "\t";

	public function printType(Type $type): string
	{
		return $this->print($type, 0);
	}

	protected function print(Type $type, ?int $level): string
	{
		if ($type instanceof MappedObjectType) {
			return $this->printMappedObjectType($type, $level);
		}

		if ($type instanceof CompoundType) {
			return $this->printCompoundType($type, $level);
		}

		if ($type instanceof ArrayType) {
			return $this->printArrayType($type, $level);
		}

		if ($type instanceof ListType) {
			return $this->printListType($type, $level);
		}

		if ($type instanceof SimpleValueType) {
			return $this->printSimpleValueType($type, $level);
		}

		if ($type instanceof EnumType) {
			return $this->printEnumType($type, $level);
		}

		if ($type instanceof MessageType) {
			return $this->printMessageType($type);
		}

		throw InvalidArgument::create()
			->withMessage(sprintf('Unsupported type %s', get_class($type)));
	}

	protected function printMappedObjectType(MappedObjectType $type, ?int $level): string
	{
		$formatted = '';
		$innerLevel = $this->getInnerIndentationLevelCount($level);

		$fields = $this->filterFields($type);
		$lastFieldKey = array_key_last($fields);

		foreach ($fields as $fieldName => $fieldType) {
			$formattedField = sprintf(
				'%s%s%s',
				$fieldName,
				$this->pathAndTypeSeparator,
				$this->print($fieldType, $innerLevel),
			);
			$formatted .= $this->printComplexTypeInnerLine($formattedField, $innerLevel, $fieldName === $lastFieldKey);
		}

		$formatted = sprintf(
			'structure%s%s%s%s',
			$this->typeAndParametersSeparator,
			$this->printComplexTypeLeftBracket('[', $level),
			$formatted,
			$this->printComplexTypeRightBracket(']', $level),
		);

		return $formatted;
	}

	/**
	 * @return array<Type>
	 */
	protected function filterFields(MappedObjectType $type): array
	{
		return $type->getFields();
	}

	protected function printCompoundType(CompoundType $type, ?int $level): string
	{
		$formatted = '';
		$subtypes = $type->getSubtypes();
		$lastKey = array_key_last($subtypes);

		foreach ($subtypes as $key => $subtype) {
			$separator = $key === $lastKey ? '' : $type->getOperator();
			$formatted .= sprintf('%s%s', $this->print($subtype, $level), $separator);
		}

		return $formatted;
	}

	protected function printArrayType(ArrayType $type, ?int $level): string
	{
		$keyType = $type->getKeyType();
		$itemType = $type->getItemType();

		$parameters = $this->printParameters($type, $level);

		$formatted = sprintf('array%s%s', $this->typeAndParametersSeparator, $parameters);
		$formatted .= $keyType !== null
			? sprintf('<%s%s%s>', $this->print($keyType, null), $this->parameterSeparator, $this->print($itemType, 0))
			: sprintf('<%s>', $this->print($itemType, 0));

		return $formatted;
	}

	protected function printListType(ListType $type, ?int $level): string
	{
		$parameters = $this->printParameters($type, $level);

		$formatted = sprintf('list%s%s', $this->typeAndParametersSeparator, $parameters);
		$formatted .= sprintf('<%s>', $this->print($type->getItemType(), 0));

		return $formatted;
	}

	protected function printSimpleValueType(SimpleValueType $type, ?int $level): string
	{
		return sprintf('%s%s', $type->getName(), $this->printParameters($type, $level));
	}

	protected function printEnumType(EnumType $type, ?int $level): string
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

	protected function printParameters(ParametrizedType $type, ?int $level): string
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
					$this->valueToString($parameter->getValue(), true, $level),
					$separator,
				)
				: sprintf('%s%s', $this->valueToString($key, false), $separator);
		}

		return sprintf('%s(%s)', $this->typeAndParametersSeparator, $inlineParameters);
	}

	/**
	 * @param mixed $value
	 */
	protected function valueToString($value, bool $includeApostrophe = true, ?int $level = 0): string
	{
		return Dumper::dumpValue($value, [
			Dumper::OptIncludeApostrophe => $includeApostrophe,
			Dumper::OptLevel => $level,
			Dumper::OptIndentChar => $this->itemsSeparator,
		]);
	}

	protected function printComplexTypeInnerLine(string $inner, ?int $level, bool $isLast): string
	{
		return sprintf('%s%s%s', $this->levelToIndent($level), $inner, $isLast ? '' : $this->itemsSeparator);
	}

	protected function printComplexTypeInnerBlock(string $inner, ?int $level): string
	{
		return str_replace($this->itemsSeparator, $this->itemsSeparator . $this->levelToIndent($level), $inner);
	}

	protected function printComplexTypeLeftBracket(string $bracket, ?int $level): string
	{
		return sprintf('%s%s', $bracket, $this->aroundItemsSeparator);
	}

	protected function printComplexTypeRightBracket(string $bracket, ?int $level): string
	{
		$leftIndentation = $level === null ? '' : str_repeat($this->itemsIndentation, $level);

		return sprintf('%s%s%s', $this->aroundItemsSeparator, $leftIndentation, $bracket);
	}

	protected function getInnerIndentationLevelCount(?int $level): ?int
	{
		return $level === null
			? null
			: $level + 1;
	}

	protected function levelToIndent(?int $level): string
	{
		return $level === null ? '' : str_repeat($this->itemsIndentation, $level);
	}

}
