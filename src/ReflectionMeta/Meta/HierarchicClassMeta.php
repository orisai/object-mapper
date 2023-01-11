<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\ReflectionMeta\Meta;

use Orisai\SourceMap\AboveReflectorSource;
use Orisai\SourceMap\ClassSource;

/**
 * @template T of object
 * @implements Meta<T, ClassSource>
 */
final class HierarchicClassMeta implements Meta
{

	/** @var AboveReflectorSource<ClassSource> */
	private AboveReflectorSource $source;

	/** @var list<T> */
	private array $attributes;

	/** @var HierarchicClassMeta<T>|null */
	private ?HierarchicClassMeta $parent;

	/** @var list<HierarchicClassMeta<T>> */
	private array $interfaces;

	/** @var list<HierarchicClassMeta<T>> */
	private array $traits;

	/** @var list<ClassConstantMeta<T>> */
	private array $constants;

	/** @var list<PropertyMeta<T>> */
	private array $properties;

	/** @var list<MethodMeta<T>> */
	private array $methods;

	/**
	 * @param AboveReflectorSource<ClassSource> $source
	 * @param list<T>                           $attributes
	 * @param HierarchicClassMeta<T>|null       $parent
	 * @param list<HierarchicClassMeta<T>>      $interfaces
	 * @param list<HierarchicClassMeta<T>>      $traits
	 * @param list<ClassConstantMeta<T>>        $constants
	 * @param list<PropertyMeta<T>>             $properties
	 * @param list<MethodMeta<T>>               $methods
	 */
	public function __construct(
		AboveReflectorSource $source,
		?HierarchicClassMeta $parent,
		array $interfaces,
		array $traits,
		array $attributes,
		array $constants,
		array $properties,
		array $methods
	)
	{
		$this->source = $source;
		$this->attributes = $attributes;
		$this->parent = $parent;
		$this->interfaces = $interfaces;
		$this->traits = $traits;
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
	 * @return HierarchicClassMeta<T>|null
	 */
	public function getParent(): ?HierarchicClassMeta
	{
		return $this->parent;
	}

	/**
	 * @return list<HierarchicClassMeta<T>>
	 */
	public function getInterfaces(): array
	{
		return $this->interfaces;
	}

	/**
	 * @return list<HierarchicClassMeta<T>>
	 */
	public function getTraits(): array
	{
		return $this->traits;
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
