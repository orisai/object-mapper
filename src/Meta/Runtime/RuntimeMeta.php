<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Meta\Runtime;

/**
 * Value object for meta returned from MetaLoader
 *
 * @readonly
 */
final class RuntimeMeta
{

	public ClassRuntimeMeta $class;

	/** @var array<int|string, FieldRuntimeMeta> */
	public array $fields;

	/**
	 * @param array<int|string, FieldRuntimeMeta> $fields
	 */
	public function __construct(ClassRuntimeMeta $class, array $fields)
	{
		$this->class = $class;
		$this->fields = $fields;
	}

	/**
	 * @return array<mixed>
	 */
	public function __serialize(): array
	{
		return [
			'class' => $this->class,
			'fields' => $this->fields,
		];
	}

	/**
	 * @param array<mixed> $data
	 */
	public function __unserialize(array $data): void
	{
		$this->class = $data['class'];
		$this->fields = $data['fields'];
	}

}
