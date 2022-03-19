<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Meta\Compile;

final class CompileMeta
{

	private ClassCompileMeta $class;

	/** @var array<string, PropertyCompileMeta> */
	private array $properties;

	/**
	 * @param array<string, PropertyCompileMeta> $properties
	 */
	public function __construct(ClassCompileMeta $class, array $properties)
	{
		$this->class = $class;
		$this->properties = $properties;
	}

	public function getClass(): ClassCompileMeta
	{
		return $this->class;
	}

	/**
	 * @return array<string, PropertyCompileMeta>
	 */
	public function getProperties(): array
	{
		return $this->properties;
	}

}
