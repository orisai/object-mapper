<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\ReflectionMeta\Meta;

use Orisai\SourceMap\AboveReflectorSource;
use Orisai\SourceMap\MethodSource;

/**
 * @template T of object
 * @implements Meta<T, MethodSource>
 */
final class MethodMeta implements Meta
{

	/** @var AboveReflectorSource<MethodSource> */
	private AboveReflectorSource $source;

	/** @var list<T> */
	private array $attributes;

	/** @var list<ParameterMeta<T>> */
	private array $parameters;

	/**
	 * @param AboveReflectorSource<MethodSource> $source
	 * @param list<T>                            $attributes
	 * @param list<ParameterMeta<T>>             $parameters
	 */
	public function __construct(AboveReflectorSource $source, array $attributes, array $parameters)
	{
		$this->source = $source;
		$this->attributes = $attributes;
		$this->parameters = $parameters;
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
	 * @return list<ParameterMeta<T>>
	 */
	public function getParameters(): array
	{
		return $this->parameters;
	}

}
