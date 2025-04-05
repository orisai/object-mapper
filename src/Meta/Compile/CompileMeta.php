<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Meta\Compile;

use Orisai\SourceMap\ClassSource;
use Orisai\SourceMap\FileSource;

final class CompileMeta
{

	/** @var list<ClassCompileMeta> */
	private array $classes;

	/** @var list<non-empty-list<FieldCompileMeta>> */
	private array $fields;

	/** @var list<ClassSource|FileSource> */
	private array $sources;

	private string $sourceName;

	/**
	 * @param list<ClassCompileMeta> $classes
	 * @param list<non-empty-list<FieldCompileMeta>> $fields
	 * @param list<ClassSource|FileSource> $sources
	 */
	public function __construct(array $classes, array $fields, array $sources, string $sourceName)
	{
		$this->classes = $classes;
		$this->fields = $fields;
		$this->sources = $sources;
		$this->sourceName = $sourceName;
	}

	/**
	 * @return list<ClassCompileMeta>
	 */
	public function getClasses(): array
	{
		return $this->classes;
	}

	/**
	 * @return list<non-empty-list<FieldCompileMeta>>
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

	public function hasAnyMeta(): bool
	{
		if ($this->fields !== []) {
			return true;
		}

		foreach ($this->classes as $class) {
			if ($class->hasAnyMeta()) {
				return true;
			}
		}

		return false;
	}

	public function getSourceName(): string
	{
		return $this->sourceName;
	}

}
