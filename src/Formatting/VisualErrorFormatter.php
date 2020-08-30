<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Formatting;

use Orisai\Exceptions\Logic\InvalidArgument;
use Orisai\ObjectMapper\Exception\InvalidData;
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
use function implode;
use function sprintf;
use function str_repeat;
use function str_replace;

class VisualErrorFormatter implements ErrorFormatter, TypeFormatter
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

	protected FormattingScopes $scopes;

	public function __construct()
	{
		$this->scopes = $this->createScopes();
	}

	/**
	 * @param array<string> $pathNodes
	 */
	public function formatError(InvalidData $exception, array $pathNodes = []): string
	{
		$this->scopes->open();

		$rootPath = implode($this->pathNodeSeparator, $pathNodes);
		$formatted = '';
		$type = $exception->getInvalidType();

		$errors = $type->getErrors();
		$fields = $this->filterFields($type);
		$lastFieldKey = array_key_last($fields);

		foreach ($fields as $fieldName => $fieldType) {
			if ($errors !== []) {
				$fieldSeparator = $this->itemsSeparator;
			} else {
				$fieldSeparator = $fieldName === $lastFieldKey ? '' : $this->itemsSeparator;
			}

			$this->scopes->openScope($type->isInvalid() ? false : !$this->scopes->shouldRenderValid());

			$formatted .= sprintf('%s%s%s%s%s', $rootPath !== '' ? $rootPath . $this->pathNodeSeparator : '', $fieldName, $this->pathAndTypeSeparator, $this->format($fieldType, 0), $fieldSeparator);

			$this->scopes->closeScope();
		}

		$lastErrorKey = array_key_last($errors);
		$this->scopes->openScope(false);

		foreach ($errors as $errorKey => $error) {
			$fieldSeparator = $errorKey === $lastErrorKey ? '' : $this->itemsSeparator;
			$formatted .= sprintf('%s%s%s', $rootPath !== '' ? $rootPath . $this->pathNodeSeparator : '', $this->format($error, 0), $fieldSeparator);
		}

		$this->scopes->closeScope();

		$this->scopes->close();

		return $formatted;
	}

	public function formatType(Type $type): string
	{
		$this->scopes->open();
		$formatted = $this->format($type, 0);
		$this->scopes->close();

		return $formatted;
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

		$errors = $type->getErrors();
		$fields = $this->filterFields($type);
		$lastFieldKey = array_key_last($fields);

		foreach ($fields as $fieldName => $fieldType) {
			$this->scopes->openScope($type->isInvalid() ? false : !$this->scopes->shouldRenderValid());

			$formattedField = sprintf('%s%s%s', $fieldName, $this->pathAndTypeSeparator, $this->format($fieldType, $innerLevel));
			$formatted .= $this->formatComplexTypeInnerLine($formattedField, $innerLevel, $errors === [] && $fieldName === $lastFieldKey);

			$this->scopes->closeScope();
		}

		$lastErrorKey = array_key_last($errors);
		$this->scopes->openScope(false);

		foreach ($errors as $errorKey => $error) {
			$formattedError = $this->format($error, $innerLevel);
			$formatted .= $this->formatComplexTypeInnerLine($formattedError, $innerLevel, $errorKey === $lastErrorKey);
		}

		$this->scopes->closeScope();

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
		if ($this->scopes->shouldRenderValid() || $type->isInvalid()) {
			return $type->getFields();
		}

		$filtered = [];

		foreach ($type->getFields() as $fieldName => $fieldType) {
			if ($type->isFieldInvalid($fieldName)) {
				$filtered[$fieldName] = $fieldType;
			}
		}

		return $filtered;
	}

	protected function formatCompoundType(CompoundType $type, ?int $level): string
	{
		$subtypes = [];

		if ($this->scopes->shouldRenderValid()) {
			// Don't filter valid, in must render valid scope
			$subtypes = $type->getSubtypes();
		} else {
			// Filter valid subtypes
			foreach ($type->getSubtypes() as $key => $subtype) {
				if (!$type->isSubtypeValid($key)) {
					$subtypes[$key] = $subtype;
				}
			}
		}

		$formatted = '';
		$lastKey = array_key_last($subtypes);
		$operator = $type->getOperator($this->humanReadableTypes);

		foreach ($subtypes as $key => $subtype) {
			// In invalid subtype are valid nodes filtered
			// In valid and skipped subtype are valid nodes rendered completely - nodes cannot choose to filter valid
			$mustBeRendered = !$type->isSubtypeInvalid($key);
			$this->scopes->openScope(!$mustBeRendered, $mustBeRendered);

			$separator = $key === $lastKey ? '' : $operator;
			$formatted .= sprintf('%s%s', $this->format($subtype, $level), $separator);

			$this->scopes->closeScope();
		}

		return $formatted;
	}

	protected function formatArrayType(ArrayType $type, ?int $level): string
	{
		$keyType = $type->getKeyType();
		$itemType = $type->getItemType();

		$invalidPairsString = '';
		$invalidPairs = $type->getInvalidPairs();
		$lastKey = array_key_last($invalidPairs);
		$innerLevel = $this->getInnerIndentationLevelCount($level);

		$this->scopes->openScope(true);

		//TODO - otestovat, že se z nevalidních itemů nevypisuje nic navíc
		foreach ($invalidPairs as $key => [$pairKeyType, $pairItemType]) {
			$invalidPairString = sprintf('%s%s', $this->valueToString($key, false), $this->pathAndTypeSeparator);

			if ($pairKeyType !== null) {
				$invalidPairString .= $this->format($pairKeyType, null);
				$invalidPairString .= ' => ';
			}

			if ($pairItemType !== null) {
				$invalidPairString .= $this->format($pairItemType, $innerLevel);
			} else {
				$invalidPairString .= 'value';
			}

			$invalidPairsString .= $this->formatComplexTypeInnerLine($invalidPairString, $innerLevel, $key === $lastKey);
		}

		$this->scopes->closeScope();

		$this->scopes->openScope($type->isInvalid() ? false : !$this->scopes->shouldRenderValid());
		$parameters = $this->formatParameters($type, $level);
		$this->scopes->closeScope();

		$formatted = 'array';

		if ($this->scopes->shouldRenderValid() || $type->isInvalid() || $type->hasInvalidParameters()) {
			$formatted .= sprintf('%s%s', $this->typeAndParametersSeparator, $parameters);
		}

		if ($this->scopes->shouldRenderValid() || $type->isInvalid()) {
			//TODO - otestovat, že se vypíše celý složený (structure) type, pokud je celá struktura nevalidní
			$this->scopes->openScope(false, true);

			$formatted .= $keyType !== null
				? sprintf('<%s%s%s>', $this->format($keyType, null), $this->parameterSeparator, $this->format($itemType, 0))
				: sprintf('<%s>', $this->format($itemType, 0));

			$this->scopes->closeScope();
		}

		if ($invalidPairs !== []) {
			$formatted .= sprintf(
				'%s%s%s',
				$this->formatComplexTypeLeftBracket('{', $level),
				$invalidPairsString,
				$this->formatComplexTypeRightBracket('}', $level),
			);
		}

		return $formatted;
	}

	protected function formatListType(ListType $type, ?int $level): string
	{
		$invalidItemsString = '';
		$invalidItems = $type->getInvalidItems();
		$lastKey = array_key_last($invalidItems);
		$innerLevel = $this->getInnerIndentationLevelCount($level);

		//TODO - otestovat, že se z nevalidních itemů nevypisuje nic navíc
		$this->scopes->openScope(true);

		foreach ($invalidItems as $key => $invalidItem) {
			$invalidItemString = sprintf('%s%s', $this->valueToString($key, false), $this->pathAndTypeSeparator);
			$invalidItemString .= $this->format($invalidItem, $innerLevel);
			$invalidItemsString .= $this->formatComplexTypeInnerLine($invalidItemString, $innerLevel, $key === $lastKey);
		}

		$this->scopes->closeScope();

		$this->scopes->openScope($type->isInvalid() ? false : !$this->scopes->shouldRenderValid());
		$parameters = $this->formatParameters($type, $level);
		$this->scopes->closeScope();

		//TODO - invalid keys?
		$formatted = 'list';

		if ($this->scopes->shouldRenderValid() || $type->isInvalid() || $type->hasInvalidParameters()) {
			$formatted .= sprintf('%s%s', $this->typeAndParametersSeparator, $parameters);
		}

		if ($this->scopes->shouldRenderValid() || $type->isInvalid()) {
			//TODO - otestovat, že se vypíše celý složený (structure) type, pokud je celá struktura nevalidní
			$this->scopes->openScope(false, true);

			$formatted .= sprintf('<%s>', $this->format($type->getItemType(), 0));

			$this->scopes->closeScope();
		}

		if ($invalidItems !== []) {
			$formatted .= sprintf(
				'%s%s%s',
				$this->formatComplexTypeLeftBracket('{', $level),
				$invalidItemsString,
				$this->formatComplexTypeRightBracket('}', $level),
			);
		}

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

		foreach ($parameters as $parameter) {
			if ($this->scopes->shouldRenderValid()) {
				continue;
			}

			if (!$parameter->isInvalid()) {
				unset($parameters[$parameter->getKey()]);
			}
		}

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

	protected function createScopes(): FormattingScopes
	{
		return new FormattingScopes();
	}

}
