<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\ReflectionMeta\Meta;

use Orisai\SourceMap\AboveReflectorSource;
use Orisai\SourceMap\ParameterSource;

/**
 * @template T of object
 * @implements Meta<T, ParameterSource>
 */
final class ParameterMeta implements Meta
{

	/** @var AboveReflectorSource<ParameterSource> */
	private AboveReflectorSource $source;

	/** @var list<T> */
	private array $attributes;

	/**
	 * @param AboveReflectorSource<ParameterSource> $source
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
