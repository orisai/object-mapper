<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Formatting;

use Orisai\ObjectMapper\Meta\MetaLoader;
use Orisai\ObjectMapper\Types\StructureType;

class ArrayDefaultValuesFormatter implements StructureFormatter
{

	private MetaLoader $metaLoader;

	/**
	 * Placeholder for required value
	 */
	public ?string $requiredValuePlaceholder = null;

	public function __construct(MetaLoader $metaLoader)
	{
		$this->metaLoader = $metaLoader;
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
	protected function formatStructureType(StructureType $type): array
	{
		$meta = $this->metaLoader->load($type->getClass())->getProperties();
		$formatted = [];
		$fields = $type->getFields();

		foreach ($fields as $fieldName => $fieldType) {
			$defaultMeta = $meta[$fieldName]->getDefault();

			if ($fieldType instanceof StructureType) {
				$value = $this->formatStructureType($fieldType);
			} elseif ($defaultMeta->hasValue()) {
				$value = $defaultMeta->getValue();
			} elseif ($this->requiredValuePlaceholder !== null) {
				$value = $this->requiredValuePlaceholder;
			} else {
				continue;
			}

			$formatted[$fieldName] = $value;
		}

		return $formatted;
	}

}
