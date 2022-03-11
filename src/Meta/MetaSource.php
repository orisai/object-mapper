<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Meta;

use Orisai\ObjectMapper\MappedObject;
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
	 * Returns metadata in following format:
	 * [
	 * 		'class' => [
	 * 			'callbacks' => [...],
	 * 			'docs' => [...],
	 * 			'modifiers' => [...],
	 * 		],
	 * 		'properties' => [
	 * 			'propertyName' => [
	 *				// Default value, if any
	 * 				// Key is not set in case no default is available to distinguish between no value and null
	 * 				'default' => 'example',
	 *				'callbacks' => [
	 * 					[
	 * 						'type' => AnnotationClass:class,
	 * 						'args' => [],
	 * 					],
	 * 				],
	 *				'docs' => [
	 * 					[
	 * 						'type' => AnnotationClass:class,
	 * 						'args' => [],
	 * 					],
	 * 				],
	 * 				'modifiers' => [
	 * 					[
	 * 						'type' => AnnotationClass::class,
	 * 						'args' => [],
	 * 					],
	 * 				],
	 *				'rule' => [
	 * 					'type' => RuleClass::class,
	 * 					'args' => [],
	 * 				],
	 * 			],
	 * 		],
	 * ]
	 *
	 * @param ReflectionClass<MappedObject> $class
	 * @return array<mixed>
	 */
	public function load(ReflectionClass $class): array;

}
