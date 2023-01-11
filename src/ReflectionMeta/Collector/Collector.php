<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\ReflectionMeta\Collector;

use Orisai\ObjectMapper\ReflectionMeta\Meta\ClassMeta;
use ReflectionClass;

interface Collector
{

	/**
	 * @template T of object
	 * @param ReflectionClass<object> $collectedClass
	 * @param class-string<T>         $attributeClass
	 * @return non-empty-list<ClassMeta<T>>
	 */
	public function collect(ReflectionClass $collectedClass, string $attributeClass): array;

}
