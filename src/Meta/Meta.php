<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Meta;

/**
 * Value object for meta returned from MetaLoader
 */
final class Meta
{

	/** @var array<mixed> */
	private array $class;

	private ?ClassMeta $instClass = null;

	/** @var array<mixed> */
	private array $properties;

	/** @var array<PropertyMeta>|null */
	private ?array $instProperties = null;

	private function __construct()
	{
	}

	/**
	 * @param array<mixed> $meta
	 */
	public static function fromArray(array $meta): self
	{
		$self = new self();
		$self->class = $meta[MetaSource::LOCATION_CLASS] ?? [];
		$self->properties = $meta[MetaSource::LOCATION_PROPERTIES] ?? [];

		return $self;
	}

	public function getClass(): ClassMeta
	{
		if ($this->instClass !== null) {
			return $this->instClass;
		}

		return $this->instClass = ClassMeta::fromArray($this->class);
	}

	/**
	 * @return array<PropertyMeta>
	 */
	public function getProperties(): array
	{
		if ($this->instProperties !== null) {
			return $this->instProperties;
		}

		$processed = [];

		foreach ($this->properties as $name => $property) {
			$processed[$name] = PropertyMeta::fromArray($property);
		}

		return $this->instProperties = $processed;
	}

}
