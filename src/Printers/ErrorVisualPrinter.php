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
use Orisai\ObjectMapper\Types\MultiValueType;
use Orisai\ObjectMapper\Types\ParametrizedType;
use Orisai\ObjectMapper\Types\SimpleValueType;
use Orisai\ObjectMapper\Types\Type;
use Orisai\ObjectMapper\Types\TypeParameter;
use function get_class;
use function sprintf;

final class ErrorVisualPrinter implements ErrorPrinter, TypePrinter
{

	public TypeToStringConverter $converter;

	public function __construct()
	{
		$this->converter = new TypeToStringConverter();
	}

	/**
	 * @param array<string> $pathNodes
	 */
	public function printError(InvalidData $exception, array $pathNodes = []): string
	{
		$scope = PrinterScope::forInvalidScope();

		$type = $exception->getType();

		$errors = $type->getErrors();
		$printedFields = [];
		foreach ($this->filterFields($type, $scope) as $fieldName => $fieldType) {
			$fieldScope = $type->isInvalid()
				? $scope->withValidNodes()
				: $scope->withoutValidNodes();

			$printedFields[$fieldName] = $this->print($fieldType, $fieldScope);
		}

		$printedErrors = [];
		foreach ($errors as $errorKey => $error) {
			$printedErrors[$errorKey] = $this->print($error->getType(), $scope->withoutValidNodes());
		}

		return $this->converter->printError($pathNodes, $printedFields, $printedErrors);
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
		$printedFields = [];
		foreach ($this->filterFields($type, $scope) as $fieldName => $fieldType) {
			$fieldScope = $type->isInvalid() || $scope->shouldRenderValid()
				? $scope->withValidNodes()
				: $scope->withoutValidNodes();

			$printedFields[$fieldName] = $this->print($fieldType, $fieldScope);
		}

		$printedErrors = [];
		foreach ($type->getErrors() as $errorKey => $error) {
			$printedErrors[$errorKey] = $this->print($error->getType(), $scope->withoutValidNodes());
		}

		return $this->converter->printShape($printedFields, $printedErrors);
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
		$printedSubtypes = [];
		foreach ($this->getSubtypes($scope, $type) as $key => $subtype) {
			// In invalid subtype are valid nodes filtered
			// In valid and skipped subtype are valid nodes rendered completely - nodes cannot choose to filter valid
			$mustBeRendered = !$type->isSubtypeInvalid($key);
			$subtypeScope = $mustBeRendered
				? $scope->withValidNodes()->withImmutableState()
				: $scope->withoutValidNodes();

			$printedSubtypes[$key] = $this->print($subtype, $subtypeScope);
		}

		return $this->converter->printCompound($type->getOperator(), $printedSubtypes);
	}

	/**
	 * @return array<int|string, Type>
	 */
	private function getSubtypes(PrinterScope $scope, CompoundType $type): array
	{
		// Don't filter valid, in must render valid scope
		if ($scope->shouldRenderValid()) {
			return $type->getSubtypes();
		}

		// Filter valid subtypes
		$subtypes = [];
		foreach ($type->getSubtypes() as $key => $subtype) {
			if (!$type->isSubtypeValid($key)) {
				$subtypes[$key] = $subtype;
			}
		}

		return $subtypes;
	}

	private function printArrayType(ArrayType $type, PrinterScope $scope): string
	{
		$keyType = $type->getKeyType();
		$itemType = $type->getItemType();

		if ($scope->shouldRenderValid() || $type->isInvalid()) {
			//TODO - otestovat, že se vypíše celý složený (structure) type, pokud je celá struktura nevalidní
			$pairScope = $scope->withValidNodes()->withImmutableState();

			$printedKeyType = $keyType !== null ? $this->print($keyType, $pairScope) : null;
			$printedItemType = $this->print($itemType, $pairScope);
		} else {
			$printedKeyType = null;
			$printedItemType = null;
		}

		//TODO - otestovat, že se z nevalidních itemů nevypisuje nic navíc
		$invalidPairs = [];
		foreach ($type->getInvalidPairs() as $key => $pair) {
			$invalidPairScope = $scope->withoutValidNodes();

			$pairKeyType = $pair->getKey();
			$pairKey = $pairKeyType !== null
				? $this->print($pairKeyType->getType(), $invalidPairScope)
				: null;

			$pairItemType = $pair->getValue();
			$pairValue = $pairItemType !== null
				? $this->print($pairItemType->getType(), $invalidPairScope)
				: null;

			$invalidPairs[$key] = [$pairKey, $pairValue];
		}

		return $this->converter->printArray(
			'array',
			$this->getMultiValueTypeParameters($type, $scope),
			$printedKeyType,
			$printedItemType,
			$invalidPairs,
		);
	}

	private function printListType(ListType $type, PrinterScope $scope): string
	{
		//TODO - invalid keys?
		$itemType = $type->getItemType();
		if ($scope->shouldRenderValid() || $type->isInvalid()) {
			//TODO - otestovat, že se vypíše celý složený (structure) type, pokud je celá struktura nevalidní
			$pairScope = $scope->withValidNodes()->withImmutableState();

			$printedItemType = $this->print($itemType, $pairScope);
		} else {
			$printedItemType = null;
		}

		//TODO - otestovat, že se z nevalidních itemů nevypisuje nic navíc
		$invalidPairs = [];
		foreach ($type->getInvalidItems() as $key => $invalidItem) {
			$invalidItemScope = $scope->withoutValidNodes();

			$invalidItem = $this->print($invalidItem->getType(), $invalidItemScope);

			$invalidPairs[$key] = [null, $invalidItem];
		}

		return $this->converter->printArray(
			'list',
			$this->getMultiValueTypeParameters($type, $scope),
			null,
			$printedItemType,
			$invalidPairs,
		);
	}

	/**
	 * @return array<int|string, TypeParameter>
	 */
	private function getMultiValueTypeParameters(MultiValueType $type, PrinterScope $scope): array
	{
		$parametersScope = $type->isInvalid() || $scope->shouldRenderValid()
			? $scope->withValidNodes()
			: $scope->withoutValidNodes();

		return $scope->shouldRenderValid() || $type->isInvalid() || $type->hasInvalidParameters()
			? $this->getParameters($type, $parametersScope)
			: [];
	}

	private function printSimpleValueType(SimpleValueType $type, PrinterScope $scope): string
	{
		return $this->converter->printSimpleValue(
			$type->getName(),
			$this->getParameters($type, $scope),
		);
	}

	private function printEnumType(EnumType $type): string
	{
		return $this->converter->printEnum($type);
	}

	private function printMessageType(MessageType $type): string
	{
		return $this->converter->printMessage($type);
	}

	/**
	 * @return array<int|string, TypeParameter>
	 */
	private function getParameters(ParametrizedType $type, PrinterScope $scope): array
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

		return $parameters;
	}

}
