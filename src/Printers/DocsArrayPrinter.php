<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Printers;

use Orisai\ObjectMapper\Meta\MetaLoader;
use Orisai\ObjectMapper\Meta\Runtime\SharedNodeRuntimeMeta;
use Orisai\ObjectMapper\Types\ArrayType;
use Orisai\ObjectMapper\Types\CompoundType;
use Orisai\ObjectMapper\Types\MultiValueType;
use Orisai\ObjectMapper\Types\StructureType;
use Orisai\ObjectMapper\Types\Type;
use function array_key_exists;

class DocsArrayPrinter implements StructurePrinter
{

	private MetaLoader $metaLoader;

	private TypePrinter $typePrinter;

	private DefaultValuesArrayPrinter $defaultsPrinter;

	public function __construct(
		MetaLoader $metaLoader,
		?TypePrinter $typePrinter = null,
		?DefaultValuesArrayPrinter $defaultsPrinter = null
	)
	{
		$this->metaLoader = $metaLoader;
		$this->typePrinter = $typePrinter ?? new TypeVisualPrinter();
		$this->defaultsPrinter = $defaultsPrinter ?? new DefaultValuesArrayPrinter($metaLoader);
	}

	/**
	 * @return array<mixed>
	 */
	public function printType(StructureType $type): array
	{
		return $this->printStructureType($type);
	}

	/**
	 * @return array<mixed>
	 */
	private function print(Type $type): array
	{
		if ($type instanceof StructureType) {
			return $this->printStructureType($type);
		}

		if ($type instanceof MultiValueType) {
			return $this->printMultiType($type);
		}

		if ($type instanceof CompoundType) {
			return $this->printCompoundType($type);
		}

		return $this->printDefault($type);
	}

	/**
	 * @return array<mixed>
	 */
	private function printStructureType(StructureType $type): array
	{
		$meta = $this->metaLoader->load($type->getClass());
		$propertiesMeta = $meta->getProperties();

		$fields = [];
		$defaults = $this->defaultsPrinter->printType($type);

		foreach ($type->getFields() as $fieldName => $fieldType) {
			$propertyMeta = $propertiesMeta[$fieldName];

			$formattedField = [
				'docs' => $this->printDocs($propertyMeta),
				'value' => $this->print($fieldType),
			];

			if (array_key_exists($fieldName, $defaults) && !$fieldType instanceof StructureType) {
				$formattedField['default'] = $defaults[$fieldName];
			}

			$fields[$fieldName] = $formattedField;
		}

		return [
			'type' => 'structure',
			'sourceClass' => $type->getClass(),
			'docs' => $this->printDocs($meta->getClass()),
			'fields' => $fields,
		];
	}

	/**
	 * @return array<mixed>
	 */
	private function printDocs(SharedNodeRuntimeMeta $meta): array
	{
		$docs = [];

		foreach ($meta->getDocs() as $docsMeta) {
			$name = $docsMeta->getName();
			$args = $docsMeta->getArgs();
			$docs[$name] = $args;
		}

		return $docs;
	}

	/**
	 * @return array<mixed>
	 */
	private function printCompoundType(CompoundType $type): array
	{
		if (!$this->compoundContainsStructures($type)) {
			return $this->printDefault($type);
		}

		$subtypes = [];

		foreach ($type->getSubtypes() as $subtype) {
			$subtypes[] = $this->print($subtype);
		}

		return [
			'type' => 'compound',
			'short' => $this->typePrinter->printType($type),
			'subtypes' => $subtypes,
		];
	}

	/**
	 * @return array<mixed>
	 */
	private function printMultiType(MultiValueType $type): array
	{
		$keyType = $type instanceof ArrayType
			? $type->getKeyType()
			: null;

		return [
			'type' => 'array',
			'key' => $keyType !== null ? $this->print($keyType) : null,
			'item' => $this->print($type->getItemType()),
		];
	}

	/**
	 * @return array<mixed>
	 */
	private function printDefault(Type $type): array
	{
		return [
			'type' => 'simple',
			'value' => $this->typePrinter->printType($type),
		];
	}

	private function compoundContainsStructures(CompoundType $type): bool
	{
		foreach ($type->getSubtypes() as $subtype) {
			if ($subtype instanceof StructureType) {
				return true;
			}

			if ($subtype instanceof MultiValueType && $this->multiValueContainsStructure($subtype)) {
				return true;
			}
		}

		return false;
	}

	private function multiValueContainsStructure(MultiValueType $type): bool
	{
		$item = $type->getItemType();

		if ($item instanceof StructureType) {
			return true;
		}

		return $item instanceof CompoundType && $this->compoundContainsStructures($item);
	}

}
