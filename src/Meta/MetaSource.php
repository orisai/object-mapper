<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Meta;

use Orisai\ObjectMapper\MappedObject;
use Orisai\ObjectMapper\Meta\Compile\CompileMeta;
use ReflectionClass;

interface MetaSource
{

	public const
		LOCATION_CLASS = 'class',
		LOCATION_PROPERTIES = 'properties';

	public const
		TYPE_CALLBACKS = 'callbacks',
		TYPE_DEFAULT_VALUE = 'default',
		TYPE_DOCS = 'docs',
		TYPE_MODIFIERS = 'modifiers',
		TYPE_RULE = 'rule';

	public const
		OPTION_TYPE = 'type',
		OPTION_ARGS = 'args';

	/**
	 * @param ReflectionClass<MappedObject> $class
	 */
	public function load(ReflectionClass $class): CompileMeta;

}
