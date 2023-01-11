<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\ReflectionMeta\Meta;

use Orisai\SourceMap\AboveReflectorSource;
use Orisai\SourceMap\ClassConstantSource;

/**
 * @template T of object
 * @implements Meta<T, ClassConstantSource>
 */
final class ClassConstantMeta implements Meta
{

	/** @var AboveReflectorSource<ClassConstantSource> */
	private AboveReflectorSource $source;

	/** @var list<T> */
	private array $attributes;

	/**
	 * @param AboveReflectorSource<ClassConstantSource> $source
	 * @param list<T> $attributes
	 */
	public function __construct(AboveReflectorSource $source, array $attributes)
	{
		$this->source = $source;
		$this->attributes = $attributes;
	}

	public function getSource(): AboveReflectorSource
	{
		return $this->source;
	}

	/**
	 * @return list<T>
	 */
	public function getAttributes(): array
	{
		return $this->attributes;
	}

}
