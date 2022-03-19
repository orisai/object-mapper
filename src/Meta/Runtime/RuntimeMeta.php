<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Meta\Runtime;

/**
 * Value object for meta returned from MetaLoader
 */
final class RuntimeMeta
{

	private ClassRuntimeMeta $class;

	/** @var array<string, PropertyRuntimeMeta> */
	private array $properties;

	/** @var array<int|string, string> */
	private array $fieldsPropertiesMap;

	/**
	 * @param array<string, PropertyRuntimeMeta> $properties
	 * @param array<int|string, string>          $fieldsPropertiesMap
	 */
	public function __construct(ClassRuntimeMeta $class, array $properties, array $fieldsPropertiesMap)
	{
		$this->class = $class;
		$this->properties = $properties;
		$this->fieldsPropertiesMap = $fieldsPropertiesMap;
	}

	public function getClass(): ClassRuntimeMeta
	{
		return $this->class;
	}

	/**
	 * @return array<string, PropertyRuntimeMeta>
	 */
	public function getProperties(): array
	{
		return $this->properties;
	}

	/**
	 * @return array<int|string, string>
	 */
	public function getFieldsPropertiesMap(): array
	{
		return $this->fieldsPropertiesMap;
	}

}
