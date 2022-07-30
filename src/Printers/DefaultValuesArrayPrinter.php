<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Printers;

use Orisai\ObjectMapper\Meta\MetaLoader;
use Orisai\ObjectMapper\Types\MappedObjectType;

final class DefaultValuesArrayPrinter implements MappedObjectPrinter
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
	public function printType(MappedObjectType $type): array
	{
		return $this->printMappedObjectType($type);
	}

	/**
	 * @return array<mixed>
	 */
	private function printMappedObjectType(MappedObjectType $type): array
	{
		$meta = $this->metaLoader->load($type->getClass())->getProperties();
		$formatted = [];
		$fields = $type->getFields();

		foreach ($fields as $fieldName => $fieldType) {
			$defaultMeta = $meta[$fieldName]->getDefault();

			if ($fieldType instanceof MappedObjectType) {
				$value = $this->printMappedObjectType($fieldType);
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
