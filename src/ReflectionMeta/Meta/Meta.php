<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\ReflectionMeta\Meta;

use Orisai\SourceMap\AboveReflectorSource;
use Orisai\SourceMap\ReflectorSource;

/**
 * @template T of object
 * @template S of ReflectorSource
 */
interface Meta
{

	/**
	 * @return AboveReflectorSource<S>
	 */
	public function getSource(): AboveReflectorSource;

	/**
	 * @return list<T>
	 */
	public function getAttributes(): array;

}
