<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Meta;

use Orisai\ObjectMapper\MappedObject;
use Orisai\ObjectMapper\Meta\Compile\CompileMeta;
use ReflectionClass;

interface MetaSource
{

	public const
		OPTION_TYPE = 'type',
		OPTION_ARGS = 'args';

	/**
	 * @param ReflectionClass<MappedObject> $class
	 */
	public function load(ReflectionClass $class): CompileMeta;

}
