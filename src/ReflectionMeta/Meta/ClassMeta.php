<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\ReflectionMeta\Meta;

use Orisai\SourceMap\AboveReflectorSource;
use Orisai\SourceMap\ClassSource;

/**
 * @template T of object
 * @implements Meta<T, ClassSource>
 */
final class ClassMeta implements Meta
{

	/** @var AboveReflectorSource<ClassSource> */
	private AboveReflectorSource $source;

	/** @var list<T> */
	private array $attributes;

	/** @var list<ClassConstantMeta<T>> */
	private array $constants;

	/** @var list<PropertyMeta<T>> */
	private array $properties;

	/** @var list<MethodMeta<T>> */
	private array $methods;

	/**
	 * @param AboveReflectorSource<ClassSource> $source
	 * @param list<T>                           $attributes
	 * @param list<ClassConstantMeta<T>>        $constants
	 * @param list<PropertyMeta<T>>             $properties
	 * @param list<MethodMeta<T>>               $methods
	 */
	public function __construct(
		AboveReflectorSource $source,
		array $attributes,
		array $constants,
		array $properties,
		array $methods
	)
	{
		$this->source = $source;
		$this->attributes = $attributes;
		$this->constants = $constants;
		$this->properties = $properties;
		$this->methods = $methods;
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

	/**
	 * @return list<ClassConstantMeta<T>>
	 */
	public function getConstants(): array
	{
		return $this->constants;
	}

	/**
	 * @return list<PropertyMeta<T>>
	 */
	public function getProperties(): array
	{
		return $this->properties;
	}

	/**
	 * @return list<MethodMeta<T>>
	 */
	public function getMethods(): array
	{
		return $this->methods;
	}

}
