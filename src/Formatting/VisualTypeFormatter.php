<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Formatting;

use Orisai\Exceptions\Logic\InvalidArgument;
use Orisai\ObjectMapper\Types\ArrayType;
use Orisai\ObjectMapper\Types\CompoundType;
use Orisai\ObjectMapper\Types\EnumType;
use Orisai\ObjectMapper\Types\ListType;
use Orisai\ObjectMapper\Types\MessageType;
use Orisai\ObjectMapper\Types\ParametrizedType;
use Orisai\ObjectMapper\Types\SimpleValueType;
use Orisai\ObjectMapper\Types\StructureType;
use Orisai\ObjectMapper\Types\Type;
use function array_key_last;
use function get_class;
use function sprintf;
use function str_repeat;
use function str_replace;

class VisualTypeFormatter implements TypeFormatter
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
	 * Separator around all items (structures fields, invalid array and list keys)
	 */
	public string $aroundItemsSeparator = "\n";

	/**
	 * Separator between items (structures fields, invalid array and list keys)
	 */
	public string $itemsSeparator = "\n";

	/**
	 * Indentation between items (structures fields, invalid array and list keys)
	 */
	public string $itemsIndentation = "\t";

	/**
	 * Replace special characters like & or | with words
	 */
	public bool $humanReadableTypes = false;

	public function formatType(Type $type): string
	{
		return $this->format($type, 0);
	}

	protected function format(Type $type, ?int $level): string
	{
		if ($type instanceof StructureType) {
			return $this->formatStructureType($type, $level);
		}

		if ($type instanceof CompoundType) {
			return $this->formatCompoundType($type, $level);
		}

		if ($type instanceof ArrayType) {
			return $this->formatArrayType($type, $level);
		}

		if ($type instanceof ListType) {
			return $this->formatListType($type, $level);
		}

		if ($type instanceof SimpleValueType) {
			return $this->formatSimpleValueType($type, $level);
		}

		if ($type instanceof EnumType) {
			return $this->formatEnumType($type, $level);
		}

		if ($type instanceof MessageType) {
			return $this->formatMessageType($type);
		}

		throw InvalidArgument::create()
			->withMessage(sprintf('Unsupported type %s', get_class($type)));
	}

	protected function formatStructureType(StructureType $type, ?int $level): string
	{
		$formatted = '';
		$innerLevel = $this->getInnerIndentationLevelCount($level);

		$fields = $this->filterFields($type);
		$lastFieldKey = array_key_last($fields);

		foreach ($fields as $fieldName => $fieldType) {
			$formattedField = sprintf('%s%s%s', $fieldName, $this->pathAndTypeSeparator, $this->format($fieldType, $innerLevel));
			$formatted .= $this->formatComplexTypeInnerLine($formattedField, $innerLevel, $fieldName === $lastFieldKey);
		}

		$formatted = sprintf(
			'structure%s%s%s%s',
			$this->typeAndParametersSeparator,
			$this->formatComplexTypeLeftBracket('[', $level),
			$formatted,
			$this->formatComplexTypeRightBracket(']', $level),
		);

		return $formatted;
	}

	/**
	 * @return array<Type>
	 */
	protected function filterFields(StructureType $type): array
	{
		return $type->getFields();
	}

	protected function formatCompoundType(CompoundType $type, ?int $level): string
	{
		$formatted = '';
		$subtypes = $type->getSubtypes();
		$lastKey = array_key_last($subtypes);

		foreach ($subtypes as $key => $subtype) {
			$separator = $key === $lastKey ? '' : $type->getOperator($this->humanReadableTypes);
			$formatted .= sprintf('%s%s', $this->format($subtype, $level), $separator);
		}

		return $formatted;
	}

	protected function formatArrayType(ArrayType $type, ?int $level): string
	{
		$keyType = $type->getKeyType();
		$itemType = $type->getItemType();

		$parameters = $this->formatParameters($type, $level);

		$formatted = sprintf('array%s%s', $this->typeAndParametersSeparator, $parameters);
		$formatted .= $keyType !== null
			? sprintf('<%s%s%s>', $this->format($keyType, null), $this->parameterSeparator, $this->format($itemType, 0))
			: sprintf('<%s>', $this->format($itemType, 0));

		return $formatted;
	}

	protected function formatListType(ListType $type, ?int $level): string
	{
		$parameters = $this->formatParameters($type, $level);

		$formatted = sprintf('list%s%s', $this->typeAndParametersSeparator, $parameters);
		$formatted .= sprintf('<%s>', $this->format($type->getItemType(), 0));

		return $formatted;
	}

	protected function formatSimpleValueType(SimpleValueType $type, ?int $level): string
	{
		return sprintf('%s%s', $type->getType(), $this->formatParameters($type, $level));
	}

	protected function formatEnumType(EnumType $type, ?int $level): string
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

	protected function formatMessageType(MessageType $type): string
	{
		return $type->getMessage();
	}

	protected function formatParameters(ParametrizedType $type, ?int $level): string
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
				? sprintf('%s%s%s%s', $this->valueToString($key, false), $this->parameterKeyValueSeparator, $this->valueToString($parameter->getValue(), true, $level), $separator)
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
			Dumper::OPT_INCLUDE_APOSTROPHE => $includeApostrophe,
			Dumper::OPT_LEVEL => $level,
			Dumper::OPT_INDENT_CHAR => $this->itemsSeparator,
		]);
	}

	protected function formatComplexTypeInnerLine(string $inner, ?int $level, bool $isLast): string
	{
		return sprintf('%s%s%s', $this->levelToIndent($level), $inner, $isLast ? '' : $this->itemsSeparator);
	}

	protected function formatComplexTypeInnerBlock(string $inner, ?int $level): string
	{
		return str_replace($this->itemsSeparator, $this->itemsSeparator . $this->levelToIndent($level), $inner);
	}

	protected function formatComplexTypeLeftBracket(string $bracket, ?int $level): string
	{
		return sprintf('%s%s', $bracket, $this->aroundItemsSeparator);
	}

	protected function formatComplexTypeRightBracket(string $bracket, ?int $level): string
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
