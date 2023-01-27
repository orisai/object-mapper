<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\ReflectionMeta\Meta;

use Orisai\SourceMap\AboveReflectorSource;
use Orisai\SourceMap\PropertySource;
use ReflectionProperty;

/**
 * @template T of object
 * @implements Meta<T, PropertySource>
 */
final class PropertyMeta implements Meta
{

	private ReflectionProperty $callableReflector;

	/** @var AboveReflectorSource<PropertySource> */
	private AboveReflectorSource $source;

	/** @var list<T> */
	private array $attributes;

	/**
	 * @param AboveReflectorSource<PropertySource> $source
	 * @param list<T>                 $attributes
	 */
	public function __construct(ReflectionProperty $callableReflector, AboveReflectorSource $source, array $attributes)
	{
		$this->source = $source;
		$this->attributes = $attributes;
		$this->callableReflector = $callableReflector;
	}

	public function getCallableReflector(): ReflectionProperty
	{
		return $this->callableReflector;
	}

	/**
	 * @return AboveReflectorSource<PropertySource>
	 */
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
