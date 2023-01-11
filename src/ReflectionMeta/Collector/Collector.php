<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\ReflectionMeta\Collector;

use Orisai\ObjectMapper\ReflectionMeta\Meta\HierarchicClassMeta;
use ReflectionClass;

interface Collector
{

	/**
	 * @template T of object
	 * @param ReflectionClass<object> $from
	 * @param class-string<T>         $collected
	 * @return HierarchicClassMeta<T>
	 */
	public function collect(ReflectionClass $from, string $collected): HierarchicClassMeta;

}
