<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Meta\Runtime;

/**
 * Value object for meta returned from MetaLoader
 */
final class RuntimeMeta
{

	private ClassRuntimeMeta $class;

	/** @var array<int|string, FieldRuntimeMeta> */
	private array $fields;

	/** @var array<int|string, string> */
	private array $fieldsPropertiesMap;

	/**
	 * @param array<int|string, FieldRuntimeMeta> $fields
	 * @param array<int|string, string>           $fieldsPropertiesMap
	 */
	public function __construct(ClassRuntimeMeta $class, array $fields, array $fieldsPropertiesMap)
	{
		$this->class = $class;
		$this->fields = $fields;
		$this->fieldsPropertiesMap = $fieldsPropertiesMap;
	}

	public function getClass(): ClassRuntimeMeta
	{
		return $this->class;
	}

	/**
	 * @return array<int|string, FieldRuntimeMeta>
	 */
	public function getFields(): array
	{
		return $this->fields;
	}

	/**
	 * @return array<int|string, string>
	 */
	public function getFieldsPropertiesMap(): array
	{
		return $this->fieldsPropertiesMap;
	}

}
