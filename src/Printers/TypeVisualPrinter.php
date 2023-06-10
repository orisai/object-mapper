<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Printers;

use Orisai\Exceptions\Logic\InvalidArgument;
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

/**
 * @template T of string|array
 */
final class TypeVisualPrinter implements TypePrinter
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

	public function printType(Type $type)
	{
		return $this->print($type, null);
	}

	/**
	 * @return T
	 */
	private function print(Type $type, ?Type $parent)
	{
		if ($type instanceof ArrayShapeType) {
			return $this->printArrayShapeType($type);
		}

		if ($type instanceof CompoundType) {
			return $this->printCompoundType($type, $parent);
		}

		if ($type instanceof GenericArrayType) {
			return $this->printArrayType($type);
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

		$typeName = get_class($type);

		throw InvalidArgument::create()
			->withMessage("Unsupported type '$typeName'.");
	}

	/**
	 * @return T
	 */
	private function printArrayShapeType(ArrayShapeType $type)
	{
		$printedFields = [];
		foreach ($this->filterFields($type) as $fieldName => $fieldType) {
			$printedFields[$fieldName] = $this->print($fieldType, $type);
		}

		return $this->converter->printShape($printedFields);
	}

	/**
	 * @return array<Type>
	 */
	private function filterFields(ArrayShapeType $type): array
	{
		return $type->getFields();
	}

	/**
	 * @return T
	 */
	private function printCompoundType(CompoundType $type, ?Type $parent)
	{
		$printedSubtypes = [];
		foreach ($type->getSubtypes() as $key => $subtype) {
			$printedSubtypes[$key] = $this->print($subtype, $type);
		}

		return $this->converter->printCompound(
			$type->getOperator(),
			$printedSubtypes,
			$parent instanceof CompoundType,
		);
	}

	/**
	 * @return T
	 */
	private function printArrayType(GenericArrayType $type)
	{
		$keyType = $type->getKeyType();
		$printedKeyType = $keyType !== null ? $this->print($keyType, $type) : null;

		return $this->converter->printArray(
			$type->getName(),
			$this->getParameters($type),
			$printedKeyType,
			$this->print($type->getItemType(), $type),
		);
	}

	/**
	 * @return T
	 */
	private function printSimpleValueType(SimpleValueType $type)
	{
		return $this->converter->printSimpleValue(
			$type->getName(),
			$this->getParameters($type),
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
	private function getParameters(ParametrizedType $type): array
	{
		return $type->getParameters();
	}

}
