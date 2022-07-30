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
use function explode;
use function get_class;
use function implode;
use function sprintf;
use const PHP_EOL;

final class ErrorVisualPrinter implements ErrorPrinter, TypePrinter
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
	 *
	 * @var non-empty-string
	 */
	public string $itemsSeparator = PHP_EOL;

	/**
	 * Indentation between items (object fields, invalid array and list keys)
	 */
	public string $itemsIndentation = "\t";

	private PrinterScopes $scopes;

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

			$this->scopes->openScope(!$type->isInvalid() && !$this->scopes->shouldRenderValid());

			$formatted .= sprintf(
				'%s%s%s%s%s',
				$rootPath !== '' ? $rootPath . $this->pathNodeSeparator : '',
				$fieldName,
				$this->pathAndTypeSeparator,
				$this->print($fieldType),
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
				$this->print($error->getType()),
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
		$formatted = $this->print($type);
		$this->scopes->close();

		return $formatted;
	}

	private function print(Type $type): string
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

	private function printMappedObjectType(MappedObjectType $type): string
	{
		$formatted = '';

		$errors = $type->getErrors();
		$fields = $this->filterFields($type);
		$lastFieldKey = array_key_last($fields);

		foreach ($fields as $fieldName => $fieldType) {
			$this->scopes->openScope(!$type->isInvalid() && !$this->scopes->shouldRenderValid());

			$formattedField = sprintf(
				'%s%s%s',
				$fieldName,
				$this->pathAndTypeSeparator,
				$this->print($fieldType),
			);
			$formatted .= $this->printComplexTypeInnerLine(
				$formattedField,
				$errors === [] && $fieldName === $lastFieldKey,
			);

			$this->scopes->closeScope();
		}

		$lastErrorKey = array_key_last($errors);
		$this->scopes->openScope(false);

		foreach ($errors as $errorKey => $error) {
			$formattedError = $this->print($error->getType());
			$formatted .= $this->printComplexTypeInnerLine($formattedError, $errorKey === $lastErrorKey);
		}

		$this->scopes->closeScope();

		if ($formatted === '') {
			return "structure$this->typeAndParametersSeparator[]";
		}

		return "structure$this->typeAndParametersSeparator[$this->aroundItemsSeparator{$this->indent($formatted)}$this->aroundItemsSeparator]";
	}

	/**
	 * @return array<Type>
	 */
	private function filterFields(MappedObjectType $type): array
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

	private function printCompoundType(CompoundType $type): string
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
			$formatted .= sprintf('%s%s', $this->print($subtype), $separator);

			$this->scopes->closeScope();
		}

		return $formatted;
	}

	private function printArrayType(ArrayType $type): string
	{
		$keyType = $type->getKeyType();
		$itemType = $type->getItemType();

		$invalidPairsString = '';
		$invalidPairs = $type->getInvalidPairs();
		$lastKey = array_key_last($invalidPairs);

		$this->scopes->openScope(true);

		//TODO - otestovat, že se z nevalidních itemů nevypisuje nic navíc
		foreach ($invalidPairs as $key => $pair) {
			$invalidPairString = sprintf('%s%s', $this->valueToString($key, false), $this->pathAndTypeSeparator);

			$pairKeyType = $pair->getKey();
			if ($pairKeyType !== null) {
				$invalidPairString .= $this->print($pairKeyType->getType());
				$invalidPairString .= ' => ';
			}

			$pairItemType = $pair->getValue();
			if ($pairItemType !== null) {
				$invalidPairString .= $this->print($pairItemType->getType());
			} else {
				$invalidPairString .= 'value';
			}

			$invalidPairsString .= $this->printComplexTypeInnerLine(
				$invalidPairString,
				$key === $lastKey,
			);
		}

		$this->scopes->closeScope();

		$this->scopes->openScope(!$type->isInvalid() && !$this->scopes->shouldRenderValid());
		$parameters = $this->printParameters($type);
		$this->scopes->closeScope();

		$formatted = '';

		if ($this->scopes->shouldRenderValid() || $type->isInvalid() || $type->hasInvalidParameters()) {
			$formatted .= sprintf('%s%s', $this->typeAndParametersSeparator, $parameters);
		}

		if ($this->scopes->shouldRenderValid() || $type->isInvalid()) {
			//TODO - otestovat, že se vypíše celý složený (structure) type, pokud je celá struktura nevalidní
			$this->scopes->openScope(false, true);

			$formatted .= $keyType !== null
				? sprintf(
					'<%s%s%s>',
					$this->print($keyType),
					$this->parameterSeparator,
					$this->print($itemType),
				)
				: sprintf('<%s>', $this->print($itemType));

			$this->scopes->closeScope();
		}

		if ($invalidPairs !== []) {
			$formatted .= "{{$this->aroundItemsSeparator}{$this->indent($invalidPairsString)}{$this->aroundItemsSeparator}}";
		}

		return "array$formatted";
	}

	private function printListType(ListType $type): string
	{
		$invalidItemsString = '';
		$invalidItems = $type->getInvalidItems();
		$lastKey = array_key_last($invalidItems);

		//TODO - otestovat, že se z nevalidních itemů nevypisuje nic navíc
		$this->scopes->openScope(true);

		foreach ($invalidItems as $key => $invalidItem) {
			$invalidItemString = sprintf('%s%s', $this->valueToString($key, false), $this->pathAndTypeSeparator);
			$invalidItemString .= $this->print($invalidItem->getType());
			$invalidItemsString .= $this->printComplexTypeInnerLine(
				$invalidItemString,
				$key === $lastKey,
			);
		}

		$this->scopes->closeScope();

		$this->scopes->openScope(!$type->isInvalid() && !$this->scopes->shouldRenderValid());
		$parameters = $this->printParameters($type);
		$this->scopes->closeScope();

		//TODO - invalid keys?
		$formatted = '';

		if ($this->scopes->shouldRenderValid() || $type->isInvalid() || $type->hasInvalidParameters()) {
			$formatted .= sprintf('%s%s', $this->typeAndParametersSeparator, $parameters);
		}

		if ($this->scopes->shouldRenderValid() || $type->isInvalid()) {
			//TODO - otestovat, že se vypíše celý složený (structure) type, pokud je celá struktura nevalidní
			$this->scopes->openScope(false, true);

			$formatted .= sprintf('<%s>', $this->print($type->getItemType()));

			$this->scopes->closeScope();
		}

		if ($invalidItems !== []) {
			$formatted .= "{{$this->aroundItemsSeparator}{$this->indent($invalidItemsString)}{$this->aroundItemsSeparator}}";
		}

		return "list$formatted";
	}

	private function printSimpleValueType(SimpleValueType $type): string
	{
		return sprintf('%s%s', $type->getName(), $this->printParameters($type));
	}

	private function printEnumType(EnumType $type): string
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

	private function printMessageType(MessageType $type): string
	{
		return $type->getMessage();
	}

	private function printParameters(ParametrizedType $type): string
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
	private function valueToString($value, bool $includeApostrophe = true): string
	{
		return Dumper::dumpValue($value, [
			Dumper::OptIncludeApostrophe => $includeApostrophe,
			Dumper::OptLevel => 1,
			Dumper::OptIndentChar => $this->itemsSeparator,
		]);
	}

	private function printComplexTypeInnerLine(string $inner, bool $isLast): string
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

	private function createScopes(): PrinterScopes
	{
		return new PrinterScopes();
	}

}
