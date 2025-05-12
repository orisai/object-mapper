<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Meta\Compile;

use Orisai\SourceMap\ClassSource;
use Orisai\SourceMap\FileSource;
use ReflectionClass;
use function array_key_last;

final class CompileMeta
{

	/** @var non-empty-list<ClassCompileMeta> */
	private array $classes;

	/** @var array<string, non-empty-list<FieldCompileMeta>> */
	private array $groupedProperties;

	/** @var list<ClassSource|FileSource> */
	private array $sources;

	private string $sourceName;

	private string $anyOfSourceKey;

	private string $allOfSourceKey;

	/**
	 * @param non-empty-list<ClassCompileMeta> $classes
	 * @param array<string, non-empty-list<FieldCompileMeta>> $groupedProperties
	 * @param list<ClassSource|FileSource> $sources
	 */
	public function __construct(
		array $classes,
		array $groupedProperties,
		array $sources,
		string $sourceName,
		string $anyOfSourceKey,
		string $allOfSourceKey
	)
	{
		$this->classes = $classes;
		$this->groupedProperties = $groupedProperties;
		$this->sources = $sources;
		$this->sourceName = $sourceName;
		$this->anyOfSourceKey = $anyOfSourceKey;
		$this->allOfSourceKey = $allOfSourceKey;
	}

	/**
	 * @return ReflectionClass<object>
	 */
	public function getRootClass(): ReflectionClass
	{
		return $this->classes[array_key_last($this->classes)]->getClass()->getSource()->getReflector();
	}

	/**
	 * @return non-empty-list<ClassCompileMeta>
	 */
	public function getClasses(): array
	{
		return $this->classes;
	}

	/**
	 * @return array<string, list<FieldCompileMeta>>
	 */
	public function getGroupedProperties(): array
	{
		return $this->groupedProperties;
	}

	/**
	 * @return list<ClassSource|FileSource>
	 */
	public function getSources(): array
	{
		return $this->sources;
	}

	public function hasAnyDefinitions(): bool
	{
		foreach ($this->groupedProperties as $fieldMetas) {
			foreach ($fieldMetas as $fieldMeta) {
				if ($fieldMeta->getDefinitions() !== []) {
					return true;
				}
			}
		}

		foreach ($this->classes as $class) {
			if ($class->getDefinitions() !== []) {
				return true;
			}
		}

		return false;
	}

	public function getSourceName(): string
	{
		//TODO - vyřešit jinak, vyhodit source keys
		return $this->sourceName;
	}

	public function getAnyOfSourceKey(): string
	{
		return $this->anyOfSourceKey;
	}

	public function getAllOfSourceKey(): string
	{
		return $this->allOfSourceKey;
	}

}
