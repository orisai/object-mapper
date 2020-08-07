<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Formatting;

use Orisai\ObjectMapper\Meta\MetaLoader;
use Orisai\ObjectMapper\Meta\SharedMeta;
use Orisai\ObjectMapper\Types\ArrayType;
use Orisai\ObjectMapper\Types\CompoundType;
use Orisai\ObjectMapper\Types\MultiValueType;
use Orisai\ObjectMapper\Types\StructureType;
use Orisai\ObjectMapper\Types\Type;
use function array_key_exists;

class ArrayDocsFormatter implements StructureFormatter
{

	private MetaLoader $metaLoader;
	private TypeFormatter $typeFormatter;
	private ArrayDefaultValuesFormatter $defaultsFormatter;

	public function __construct(
		MetaLoader $metaLoader,
		?TypeFormatter $typeFormatter = null,
		?ArrayDefaultValuesFormatter $defaultsFormatter = null
	)
	{
		$this->metaLoader = $metaLoader;
		$this->typeFormatter = $typeFormatter ?? new VisualTypeFormatter();
		$this->defaultsFormatter = $defaultsFormatter ?? new ArrayDefaultValuesFormatter($metaLoader);
	}

	/**
	 * @return array<mixed>
	 */
	public function formatType(StructureType $type): array
	{
		return $this->formatStructureType($type);
	}

	/**
	 * @return array<mixed>
	 */
	private function format(Type $type): array
	{
		if ($type instanceof StructureType) {
			return $this->formatStructureType($type);
		}

		if ($type instanceof MultiValueType) {
			return $this->formatMultiType($type);
		}

		if ($type instanceof CompoundType) {
			return $this->formatCompoundType($type);
		}

		return $this->formatDefault($type);
	}

	/**
	 * @return array<mixed>
	 */
	private function formatStructureType(StructureType $type): array
	{
		$meta = $this->metaLoader->load($type->getClass());
		$propertiesMeta = $meta->getProperties();

		$fields = [];
		$defaults = $this->defaultsFormatter->formatType($type);

		foreach ($type->getFields() as $fieldName => $fieldType) {
			$propertyMeta = $propertiesMeta[$fieldName];

			$formattedField = [
				'docs' => $this->formatDocs($propertyMeta),
				'value' => $this->format($fieldType),
			];

			if (array_key_exists($fieldName, $defaults) && !$fieldType instanceof StructureType) {
				$formattedField['default'] = $defaults[$fieldName];
			}

			$fields[$fieldName] = $formattedField;
		}

		return [
			'type' => 'structure',
			'sourceClass' => $type->getClass(),
			'docs' => $this->formatDocs($meta->getClass()),
			'fields' => $fields,
		];
	}

	/**
	 * @return array<mixed>
	 */
	private function formatDocs(SharedMeta $meta): array
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
	private function formatCompoundType(CompoundType $type): array
	{
		if (!$this->compoundContainsStructures($type)) {
			return $this->formatDefault($type);
		}

		$subtypes = [];

		foreach ($type->getSubtypes() as $subtype) {
			$subtypes[] = $this->format($subtype);
		}

		return [
			'type' => 'compound',
			'short' => $this->typeFormatter->formatType($type),
			'subtypes' => $subtypes,
		];
	}

	/**
	 * @return array<mixed>
	 */
	private function formatMultiType(MultiValueType $type): array
	{
		$keyType = $type instanceof ArrayType
			? $type->getKeyType()
			: null;

		return [
			'type' => 'array',
			'key' => $keyType !== null ? $this->format($keyType) : null,
			'item' => $this->format($type->getItemType()),
		];
	}

	/**
	 * @return array<mixed>
	 */
	private function formatDefault(Type $type): array
	{
		return [
			'type' => 'simple',
			'value' => $this->typeFormatter->formatType($type),
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
