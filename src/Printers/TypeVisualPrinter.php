<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Printers;

use Orisai\Exceptions\Logic\InvalidArgument;
use Orisai\ObjectMapper\Types\ArrayType;
use Orisai\ObjectMapper\Types\CompoundType;
use Orisai\ObjectMapper\Types\EnumType;
use Orisai\ObjectMapper\Types\MappedObjectType;
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
final class TypeVisualPrinter implements TypePrinter
{

	/** @var TypeToPrimitiveConverter<T> */
	public TypeToPrimitiveConverter $converter;

	/**
	 * @param TypeToPrimitiveConverter<T> $converter
	 */
	public function __construct(TypeToPrimitiveConverter $converter)
	{
		$this->converter = $converter;
	}

	public function printType(Type $type)
	{
		return $this->print($type);
	}

	/**
	 * @return T
	 */
	private function print(Type $type)
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

	/**
	 * @return T
	 */
	private function printMappedObjectType(MappedObjectType $type)
	{
		$printedFields = [];
		foreach ($this->filterFields($type) as $fieldName => $fieldType) {
			$printedFields[$fieldName] = $this->print($fieldType);
		}

		return $this->converter->printShape($printedFields);
	}

	/**
	 * @return array<Type>
	 */
	private function filterFields(MappedObjectType $type): array
	{
		return $type->getFields();
	}

	/**
	 * @return T
	 */
	private function printCompoundType(CompoundType $type)
	{
		$printedSubtypes = [];
		foreach ($type->getSubtypes() as $key => $subtype) {
			$printedSubtypes[$key] = $this->print($subtype);
		}

		return $this->converter->printCompound($type->getOperator(), $printedSubtypes);
	}

	/**
	 * @return T
	 */
	private function printArrayType(ArrayType $type)
	{
		$keyType = $type->getKeyType();
		$printedKeyType = $keyType !== null ? $this->print($keyType) : null;

		return $this->converter->printArray(
			$type->getName(),
			$this->getParameters($type),
			$printedKeyType,
			$this->print($type->getItemType()),
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
		return $this->converter->printEnum($type->getValues());
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
