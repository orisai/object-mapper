<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Printers;

use Orisai\Exceptions\Logic\InvalidArgument;
use Orisai\ObjectMapper\Exception\InvalidData;
use Orisai\ObjectMapper\Types\ArrayShapeType;
use Orisai\ObjectMapper\Types\CompoundType;
use Orisai\ObjectMapper\Types\EnumType;
use Orisai\ObjectMapper\Types\GenericArrayType;
use Orisai\ObjectMapper\Types\MessageType;
use Orisai\ObjectMapper\Types\ParametrizedType;
use Orisai\ObjectMapper\Types\SimpleValueType;
use Orisai\ObjectMapper\Types\Type;
use Orisai\ObjectMapper\Types\TypeParameter;
use function get_class;
use function sprintf;

/**
 * @template T of string|array
 */
final class ErrorVisualPrinter implements ErrorPrinter, TypePrinter
{

	/** @var TypeToPrimitiveConverter<T> */
	private TypeToPrimitiveConverter $converter;

	/**
	 * @param TypeToPrimitiveConverter<T> $converter
	 */
	public function __construct(TypeToPrimitiveConverter $converter)
	{
		$this->converter = $converter;
	}

	/**
	 * @return T
	 */
	public function printError(InvalidData $exception, array $pathNodes = [])
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

	/**
	 * @return T
	 */
	public function printType(Type $type)
	{
		return $this->print($type, PrinterScope::forInvalidScope());
	}

	/**
	 * @return T
	 */
	private function print(Type $type, PrinterScope $scope)
	{
		if ($type instanceof ArrayShapeType) {
			return $this->printArrayShapeType($type, $scope);
		}

		if ($type instanceof CompoundType) {
			return $this->printCompoundType($type, $scope);
		}

		if ($type instanceof GenericArrayType) {
			return $this->printArrayType($type, $scope);
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

	/**
	 * @return T
	 */
	private function printArrayShapeType(ArrayShapeType $type, PrinterScope $scope)
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
	private function filterFields(ArrayShapeType $type, PrinterScope $scope): array
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

	/**
	 * @return T
	 */
	private function printCompoundType(CompoundType $type, PrinterScope $scope)
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

	/**
	 * @return T
	 */
	private function printArrayType(GenericArrayType $type, PrinterScope $scope)
	{
		$keyType = $type->getKeyType();
		$itemType = $type->getItemType();

		if ($scope->shouldRenderValid() || $type->isInvalid()) {
			$pairScope = $scope->withValidNodes()->withImmutableState();

			$printedKeyType = $keyType !== null ? $this->print($keyType, $pairScope) : null;
			$printedItemType = $this->print($itemType, $pairScope);
		} else {
			$printedKeyType = null;
			$printedItemType = null;
		}

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
			$type->getName(),
			$this->getArrayTypeParameters($type, $scope),
			$printedKeyType,
			$printedItemType,
			$invalidPairs,
		);
	}

	/**
	 * @return array<int|string, TypeParameter>
	 */
	private function getArrayTypeParameters(GenericArrayType $type, PrinterScope $scope): array
	{
		$parametersScope = $type->isInvalid() || $scope->shouldRenderValid()
			? $scope->withValidNodes()
			: $scope->withoutValidNodes();

		return $scope->shouldRenderValid() || $type->isInvalid() || $type->hasInvalidParameters()
			? $this->getParameters($type, $parametersScope)
			: [];
	}

	/**
	 * @return T
	 */
	private function printSimpleValueType(SimpleValueType $type, PrinterScope $scope)
	{
		return $this->converter->printSimpleValue(
			$type->getName(),
			$this->getParameters($type, $scope),
		);
	}

	/**
	 * @return T
	 */
	private function printEnumType(EnumType $type)
	{
		return $this->converter->printEnum($type->getCases());
	}

	/**
	 * @return T
	 */
	private function printMessageType(MessageType $type)
	{
		return $this->converter->printMessage($type->getMessage());
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
