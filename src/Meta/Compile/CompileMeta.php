<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Meta\Compile;

use Orisai\SourceMap\ClassSource;
use Orisai\SourceMap\FileSource;

final class CompileMeta
{

	private ClassCompileMeta $class;

	/** @var list<FieldCompileMeta> */
	private array $fields;

	/** @var list<ClassSource|FileSource> */
	private array $sources;

	/**
	 * @param list<FieldCompileMeta>       $fields
	 * @param list<ClassSource|FileSource> $sources
	 */
	public function __construct(ClassCompileMeta $class, array $fields, array $sources)
	{
		$this->class = $class;
		$this->fields = $fields;
		$this->sources = $sources;
	}

	public function getClass(): ClassCompileMeta
	{
		return $this->class;
	}

	/**
	 * @return list<FieldCompileMeta>
	 */
	public function getFields(): array
	{
		return $this->fields;
	}

	/**
	 * @return list<ClassSource|FileSource>
	 */
	public function getSources(): array
	{
		return $this->sources;
	}

	public function hasAnyAttributes(): bool
	{
		return $this->fields !== []
			|| $this->class->hasAnyAttributes();
	}

}
