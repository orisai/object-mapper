<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Printers;

use Orisai\Exceptions\Logic\InvalidArgument;
use Orisai\ObjectMapper\Exception\InvalidData;
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
use function implode;
use function sprintf;
use function str_repeat;
use function str_replace;
use const PHP_EOL;

class ErrorVisualPrinter implements ErrorPrinter, TypePrinter
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

	protected PrinterScopes $scopes;

	public function __construct()
	{
		$this->scopes = $this->createScopes();
	}

	/**
	 * @param array<string> $pathNodes
	 */
	public function printError(InvalidData $exception, array $pathNodes = []): string
	{
		$this->scopes->open();

		$rootPath = implode($this->pathNodeSeparator, $pathNodes);
		$formatted = '';
		$type = $exception->getType();

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

			$formatted .= sprintf(
				'%s%s%s%s%s',
				$rootPath !== '' ? $rootPath . $this->pathNodeSeparator : '',
				$fieldName,
				$this->pathAndTypeSeparator,
				$this->print($fieldType, 0),
				$fieldSeparator,
			);

			$this->scopes->closeScope();
		}

		$lastErrorKey = array_key_last($errors);
		$this->scopes->openScope(false);

		foreach ($errors as $errorKey => $error) {
			$fieldSeparator = $errorKey === $lastErrorKey ? '' : $this->itemsSeparator;
			$formatted .= sprintf(
				'%s%s%s',
				$rootPath !== '' ? $rootPath . $this->pathNodeSeparator : '',
				$this->print($error->getType(), 0),
				$fieldSeparator,
			);
		}

		$this->scopes->closeScope();

		$this->scopes->close();

		return $formatted;
	}

	public function printType(Type $type): string
	{
		$this->scopes->open();
		$formatted = $this->print($type, 0);
		$this->scopes->close();

		return $formatted;
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

		$errors = $type->getErrors();
		$fields = $this->filterFields($type);
		$lastFieldKey = array_key_last($fields);

		foreach ($fields as $fieldName => $fieldType) {
			$this->scopes->openScope($type->isInvalid() ? false : !$this->scopes->shouldRenderValid());

			$formattedField = sprintf(
				'%s%s%s',
				$fieldName,
				$this->pathAndTypeSeparator,
				$this->print($fieldType, $innerLevel),
			);
			$formatted .= $this->printComplexTypeInnerLine(
				$formattedField,
				$innerLevel,
				$errors === [] && $fieldName === $lastFieldKey,
			);

			$this->scopes->closeScope();
		}

		$lastErrorKey = array_key_last($errors);
		$this->scopes->openScope(false);

		foreach ($errors as $errorKey => $error) {
			$formattedError = $this->print($error->getType(), $innerLevel);
			$formatted .= $this->printComplexTypeInnerLine($formattedError, $innerLevel, $errorKey === $lastErrorKey);
		}

		$this->scopes->closeScope();

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

	protected function printCompoundType(CompoundType $type, ?int $level): string
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
		$operator = $type->getOperator();

		foreach ($subtypes as $key => $subtype) {
			// In invalid subtype are valid nodes filtered
			// In valid and skipped subtype are valid nodes rendered completely - nodes cannot choose to filter valid
			$mustBeRendered = !$type->isSubtypeInvalid($key);
			$this->scopes->openScope(!$mustBeRendered, $mustBeRendered);

			$separator = $key === $lastKey ? '' : $operator;
			$formatted .= sprintf('%s%s', $this->print($subtype, $level), $separator);

			$this->scopes->closeScope();
		}

		return $formatted;
	}

	protected function printArrayType(ArrayType $type, ?int $level): string
	{
		$keyType = $type->getKeyType();
		$itemType = $type->getItemType();

		$invalidPairsString = '';
		$invalidPairs = $type->getInvalidPairs();
		$lastKey = array_key_last($invalidPairs);
		$innerLevel = $this->getInnerIndentationLevelCount($level);

		$this->scopes->openScope(true);

		//TODO - otestovat, že se z nevalidních itemů nevypisuje nic navíc
		foreach ($invalidPairs as $key => $pair) {
			$invalidPairString = sprintf('%s%s', $this->valueToString($key, false), $this->pathAndTypeSeparator);

			$pairKeyType = $pair->getKey();
			if ($pairKeyType !== null) {
				$invalidPairString .= $this->print($pairKeyType->getType(), null);
				$invalidPairString .= ' => ';
			}

			$pairItemType = $pair->getValue();
			if ($pairItemType !== null) {
				$invalidPairString .= $this->print($pairItemType->getType(), $innerLevel);
			} else {
				$invalidPairString .= 'value';
			}

			$invalidPairsString .= $this->printComplexTypeInnerLine(
				$invalidPairString,
				$innerLevel,
				$key === $lastKey,
			);
		}

		$this->scopes->closeScope();

		$this->scopes->openScope($type->isInvalid() ? false : !$this->scopes->shouldRenderValid());
		$parameters = $this->printParameters($type, $level);
		$this->scopes->closeScope();

		$formatted = 'array';

		if ($this->scopes->shouldRenderValid() || $type->isInvalid() || $type->hasInvalidParameters()) {
			$formatted .= sprintf('%s%s', $this->typeAndParametersSeparator, $parameters);
		}

		if ($this->scopes->shouldRenderValid() || $type->isInvalid()) {
			//TODO - otestovat, že se vypíše celý složený (structure) type, pokud je celá struktura nevalidní
			$this->scopes->openScope(false, true);

			$formatted .= $keyType !== null
				? sprintf(
					'<%s%s%s>',
					$this->print($keyType, null),
					$this->parameterSeparator,
					$this->print($itemType, 0),
				)
				: sprintf('<%s>', $this->print($itemType, 0));

			$this->scopes->closeScope();
		}

		if ($invalidPairs !== []) {
			$formatted .= sprintf(
				'%s%s%s',
				$this->printComplexTypeLeftBracket('{', $level),
				$invalidPairsString,
				$this->printComplexTypeRightBracket('}', $level),
			);
		}

		return $formatted;
	}

	protected function printListType(ListType $type, ?int $level): string
	{
		$invalidItemsString = '';
		$invalidItems = $type->getInvalidItems();
		$lastKey = array_key_last($invalidItems);
		$innerLevel = $this->getInnerIndentationLevelCount($level);

		//TODO - otestovat, že se z nevalidních itemů nevypisuje nic navíc
		$this->scopes->openScope(true);

		foreach ($invalidItems as $key => $invalidItem) {
			$invalidItemString = sprintf('%s%s', $this->valueToString($key, false), $this->pathAndTypeSeparator);
			$invalidItemString .= $this->print($invalidItem->getType(), $innerLevel);
			$invalidItemsString .= $this->printComplexTypeInnerLine(
				$invalidItemString,
				$innerLevel,
				$key === $lastKey,
			);
		}

		$this->scopes->closeScope();

		$this->scopes->openScope($type->isInvalid() ? false : !$this->scopes->shouldRenderValid());
		$parameters = $this->printParameters($type, $level);
		$this->scopes->closeScope();

		//TODO - invalid keys?
		$formatted = 'list';

		if ($this->scopes->shouldRenderValid() || $type->isInvalid() || $type->hasInvalidParameters()) {
			$formatted .= sprintf('%s%s', $this->typeAndParametersSeparator, $parameters);
		}

		if ($this->scopes->shouldRenderValid() || $type->isInvalid()) {
			//TODO - otestovat, že se vypíše celý složený (structure) type, pokud je celá struktura nevalidní
			$this->scopes->openScope(false, true);

			$formatted .= sprintf('<%s>', $this->print($type->getItemType(), 0));

			$this->scopes->closeScope();
		}

		if ($invalidItems !== []) {
			$formatted .= sprintf(
				'%s%s%s',
				$this->printComplexTypeLeftBracket('{', $level),
				$invalidItemsString,
				$this->printComplexTypeRightBracket('}', $level),
			);
		}

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
			Dumper::OPT_INCLUDE_APOSTROPHE => $includeApostrophe,
			Dumper::OPT_LEVEL => $level,
			Dumper::OPT_INDENT_CHAR => $this->itemsSeparator,
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

	protected function createScopes(): PrinterScopes
	{
		return new PrinterScopes();
	}

}
