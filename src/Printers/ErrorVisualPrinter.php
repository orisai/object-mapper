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

	/**
	 * @param array<string> $pathNodes
	 */
	public function printError(InvalidData $exception, array $pathNodes = []): string
	{
		$scope = PrinterScope::forInvalidScope();

		$rootPath = implode($this->pathNodeSeparator, $pathNodes);
		$formatted = '';
		$type = $exception->getType();

		$errors = $type->getErrors();
		$fields = $this->filterFields($type, $scope);
		$lastFieldKey = array_key_last($fields);

		foreach ($fields as $fieldName => $fieldType) {
			$fieldSeparator = $errors === [] && $fieldName === $lastFieldKey
				? ''
				: $this->itemsSeparator;

			$fieldScope = $type->isInvalid()
				? $scope->withValidNodes()
				: $scope->withoutValidNodes();

			$formatted .= sprintf(
				'%s%s%s%s%s',
				$rootPath !== '' ? $rootPath . $this->pathNodeSeparator : '',
				$fieldName,
				$this->pathAndTypeSeparator,
				$this->print($fieldType, $fieldScope),
				$fieldSeparator,
			);
		}

		$lastErrorKey = array_key_last($errors);

		foreach ($errors as $errorKey => $error) {
			$fieldSeparator = $errorKey === $lastErrorKey ? '' : $this->itemsSeparator;
			$formatted .= sprintf(
				'%s%s%s',
				$rootPath !== '' ? $rootPath . $this->pathNodeSeparator : '',
				$this->print($error->getType(), $scope->withoutValidNodes()),
				$fieldSeparator,
			);
		}

		return $formatted;
	}

	public function printType(Type $type): string
	{
		return $this->print($type, PrinterScope::forInvalidScope());
	}

	private function print(Type $type, PrinterScope $scope): string
	{
		if ($type instanceof MappedObjectType) {
			return $this->printMappedObjectType($type, $scope);
		}

		if ($type instanceof CompoundType) {
			return $this->printCompoundType($type, $scope);
		}

		if ($type instanceof ArrayType) {
			return $this->printArrayType($type, $scope);
		}

		if ($type instanceof ListType) {
			return $this->printListType($type, $scope);
		}

		if ($type instanceof SimpleValueType) {
			return $this->printSimpleValueType($type, $scope);
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

	private function printMappedObjectType(MappedObjectType $type, PrinterScope $scope): string
	{
		$formatted = '';

		$errors = $type->getErrors();
		$fields = $this->filterFields($type, $scope);
		$lastFieldKey = array_key_last($fields);

		foreach ($fields as $fieldName => $fieldType) {
			$fieldScope = $type->isInvalid() || $scope->shouldRenderValid()
				? $scope->withValidNodes()
				: $scope->withoutValidNodes();

			$formattedField = sprintf(
				'%s%s%s',
				$fieldName,
				$this->pathAndTypeSeparator,
				$this->print($fieldType, $fieldScope),
			);
			$formatted .= $this->printItem(
				$formattedField,
				$errors === [] && $fieldName === $lastFieldKey,
			);
		}

		$lastErrorKey = array_key_last($errors);

		foreach ($errors as $errorKey => $error) {
			$formattedError = $this->print($error->getType(), $scope->withoutValidNodes());
			$formatted .= $this->printItem($formattedError, $errorKey === $lastErrorKey);
		}

		if ($formatted === '') {
			return "shape$this->typeAndParametersSeparator{}";
		}

		return "shape$this->typeAndParametersSeparator{{$this->aroundItemsSeparator}{$this->indent($formatted)}{$this->aroundItemsSeparator}}";
	}

	/**
	 * @return array<Type>
	 */
	private function filterFields(MappedObjectType $type, PrinterScope $scope): array
	{
		if ($scope->shouldRenderValid() || $type->isInvalid()) {
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

	private function printCompoundType(CompoundType $type, PrinterScope $scope): string
	{
		$subtypes = [];

		if ($scope->shouldRenderValid()) {
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
			$subtypeScope = $mustBeRendered
				? $scope->withValidNodes()->withImmutableState()
				: $scope->withoutValidNodes();

			$separator = $key === $lastKey ? '' : $operator;
			$formatted .= sprintf('%s%s', $this->print($subtype, $subtypeScope), $separator);
		}

		return $formatted;
	}

	private function printArrayType(ArrayType $type, PrinterScope $scope): string
	{
		$keyType = $type->getKeyType();
		$itemType = $type->getItemType();

		$invalidPairsString = '';
		$invalidPairs = $type->getInvalidPairs();
		$lastKey = array_key_last($invalidPairs);

		//TODO - otestovat, že se z nevalidních itemů nevypisuje nic navíc
		foreach ($invalidPairs as $key => $pair) {
			$invalidPairScope = $scope->withoutValidNodes();

			$invalidPairString = sprintf('%s%s', $this->printValue($key, false), $this->pathAndTypeSeparator);

			$pairKeyType = $pair->getKey();
			if ($pairKeyType !== null) {
				$invalidPairString .= $this->print($pairKeyType->getType(), $invalidPairScope);
				$invalidPairString .= ' => ';
			}

			$pairItemType = $pair->getValue();
			if ($pairItemType !== null) {
				$invalidPairString .= $this->print($pairItemType->getType(), $invalidPairScope);
			} else {
				$invalidPairString .= 'value';
			}

			$invalidPairsString .= $this->printItem(
				$invalidPairString,
				$key === $lastKey,
			);
		}

		$parametersScope = $type->isInvalid() || $scope->shouldRenderValid()
			? $scope->withValidNodes()
			: $scope->withoutValidNodes();
		$parameters = $this->printParameters($type, $parametersScope);

		$formatted = '';

		if ($scope->shouldRenderValid() || $type->isInvalid() || $type->hasInvalidParameters()) {
			$formatted .= sprintf('%s%s', $this->typeAndParametersSeparator, $parameters);
		}

		if ($scope->shouldRenderValid() || $type->isInvalid()) {
			//TODO - otestovat, že se vypíše celý složený (structure) type, pokud je celá struktura nevalidní
			$pairScope = $scope->withValidNodes()->withImmutableState();

			$formatted .= $keyType !== null
				? sprintf(
					'<%s%s%s>',
					$this->print($keyType, $pairScope),
					$this->parameterSeparator,
					$this->print($itemType, $pairScope),
				)
				: sprintf('<%s>', $this->print($itemType, $pairScope));
		}

		if ($invalidPairs !== []) {
			$formatted .= "[$this->aroundItemsSeparator{$this->indent($invalidPairsString)}$this->aroundItemsSeparator]";
		}

		return "array$formatted";
	}

	private function printListType(ListType $type, PrinterScope $scope): string
	{
		$invalidItemsString = '';
		$invalidItems = $type->getInvalidItems();
		$lastKey = array_key_last($invalidItems);

		//TODO - otestovat, že se z nevalidních itemů nevypisuje nic navíc
		foreach ($invalidItems as $key => $invalidItem) {
			$invalidItemScope = $scope->withoutValidNodes();
			$invalidItemString = sprintf('%s%s', $this->printValue($key, false), $this->pathAndTypeSeparator);
			$invalidItemString .= $this->print($invalidItem->getType(), $invalidItemScope);
			$invalidItemsString .= $this->printItem(
				$invalidItemString,
				$key === $lastKey,
			);
		}

		$parametersScope = $type->isInvalid() || $scope->shouldRenderValid()
			? $scope->withValidNodes()
			: $scope->withoutValidNodes();
		$parameters = $this->printParameters($type, $parametersScope);

		//TODO - invalid keys?
		$formatted = '';

		if ($scope->shouldRenderValid() || $type->isInvalid() || $type->hasInvalidParameters()) {
			$formatted .= sprintf('%s%s', $this->typeAndParametersSeparator, $parameters);
		}

		if ($scope->shouldRenderValid() || $type->isInvalid()) {
			//TODO - otestovat, že se vypíše celý složený (structure) type, pokud je celá struktura nevalidní
			$itemScope = $scope->withValidNodes()->withImmutableState();

			$formatted .= sprintf('<%s>', $this->print($type->getItemType(), $itemScope));
		}

		if ($invalidItems !== []) {
			$formatted .= "[$this->aroundItemsSeparator{$this->indent($invalidItemsString)}$this->aroundItemsSeparator]";
		}

		return "list$formatted";
	}

	private function printSimpleValueType(SimpleValueType $type, PrinterScope $scope): string
	{
		return sprintf('%s%s', $type->getName(), $this->printParameters($type, $scope));
	}

	private function printEnumType(EnumType $type): string
	{
		$inlineValues = '';
		$values = $type->getValues();
		$lastKey = array_key_last($values);

		foreach ($values as $key => $value) {
			$separator = $key === $lastKey ? '' : $this->parameterSeparator;
			$inlineValues .= sprintf('%s%s', $this->printValue($value, false), $separator);
		}

		return sprintf('enum(%s)', $inlineValues);
	}

	private function printMessageType(MessageType $type): string
	{
		return $type->getMessage();
	}

	private function printParameters(ParametrizedType $type, PrinterScope $scope): string
	{
		$parameters = $type->getParameters();

		foreach ($parameters as $parameter) {
			if ($scope->shouldRenderValid()) {
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
					$this->printValue($key, false),
					$this->parameterKeyValueSeparator,
					$this->printValue($parameter->getValue(), true),
					$separator,
				)
				: sprintf('%s%s', $this->printValue($key, false), $separator);
		}

		return sprintf('%s(%s)', $this->typeAndParametersSeparator, $inlineParameters);
	}

	private function printItem(string $item, bool $isLast): string
	{
		return $item . ($isLast ? '' : $this->itemsSeparator);
	}

	/**
	 * @param mixed $value
	 */
	private function printValue($value, bool $includeApostrophe = true): string
	{
		return Dumper::dumpValue($value, [
			Dumper::OptIncludeApostrophe => $includeApostrophe,
			Dumper::OptLevel => 1,
			Dumper::OptIndentChar => $this->itemsSeparator,
		]);
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
