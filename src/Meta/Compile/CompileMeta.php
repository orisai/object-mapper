<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Meta\Compile;

use Orisai\SourceMap\ClassSource;
use Orisai\SourceMap\FileSource;

final class CompileMeta
{

	private ClassCompileMeta $class;

	/** @var array<string, PropertyCompileMeta> */
	private array $properties;

	/** @var list<ClassSource|FileSource> */
	private array $sources;

	/**
	 * @param array<string, PropertyCompileMeta> $properties
	 * @param list<ClassSource|FileSource>       $sources
	 */
	public function __construct(ClassCompileMeta $class, array $properties, array $sources)
	{
		$this->class = $class;
		$this->properties = $properties;
		$this->sources = $sources;
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

	/**
	 * @return list<ClassSource|FileSource>
	 */
	public function getSources(): array
	{
		return $this->sources;
	}

}
