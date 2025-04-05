<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Meta\Source;

use Orisai\ObjectMapper\MappedObject;
use Orisai\ObjectMapper\Meta\Compile\CompileMeta;
use Orisai\ReflectionMeta\Structure\StructureGroup;
use ReflectionClass;

interface MetaSource
{

	/**
	 * @param ReflectionClass<covariant MappedObject> $rootClass
	 */
	public function load(ReflectionClass $rootClass, StructureGroup $group): CompileMeta;

}
