<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Meta\Runtime;

use Orisai\ObjectMapper\Meta\MetaResolver;
use Orisai\ObjectMapper\Meta\MetaSource;

/**
 * Value object for meta returned from MetaLoader
 */
final class RuntimeMeta
{

	/** @var array<mixed> */
	private array $class;

	private ?ClassRuntimeMeta $instClass = null;

	/** @var array<mixed> */
	private array $properties;

	/** @var array<PropertyRuntimeMeta>|null */
	private ?array $instProperties = null;

	/** @var array<int|string, string> */
	private array $fieldsPropertiesMap;

	private function __construct()
	{
		// Static constructor is required
	}

	/**
	 * @param array<mixed> $meta
	 */
	public static function fromArray(array $meta): self
	{
		$self = new self();
		$self->class = $meta[MetaSource::LOCATION_CLASS] ?? [];
		$self->properties = $meta[MetaSource::LOCATION_PROPERTIES] ?? [];
		$self->fieldsPropertiesMap = $meta[MetaResolver::FIELDS_PROPERTIES_MAP] ?? [];

		return $self;
	}

	public function getClass(): ClassRuntimeMeta
	{
		if ($this->instClass !== null) {
			return $this->instClass;
		}

		return $this->instClass = ClassRuntimeMeta::fromArray($this->class);
	}

	/**
	 * @return array<PropertyRuntimeMeta>
	 */
	public function getProperties(): array
	{
		if ($this->instProperties !== null) {
			return $this->instProperties;
		}

		$processed = [];

		foreach ($this->properties as $name => $property) {
			$processed[$name] = PropertyRuntimeMeta::fromArray($property);
		}

		return $this->instProperties = $processed;
	}

	/**
	 * @return array<int|string, string>
	 */
	public function getFieldsPropertiesMap(): array
	{
		return $this->fieldsPropertiesMap;
	}

}
