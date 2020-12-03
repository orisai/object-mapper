<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Meta;

use Orisai\ObjectMapper\ValueObject;
use ReflectionClass;

interface MetaSource
{

	public const LOCATION_CLASS = 'class';
	public const LOCATION_PROPERTIES = 'properties';

	public const TYPE_CALLBACKS = 'callbacks';
	public const TYPE_DEFAULT_VALUE = 'default';
	public const TYPE_DOCS = 'docs';
	public const TYPE_MODIFIERS = 'modifiers';
	public const TYPE_RULE = 'rule';

	public const OPTION_TYPE = 'type';
	public const OPTION_ARGS = 'args';

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
	 * @param ReflectionClass<ValueObject> $class
	 * @return array<mixed>
	 */
	public function load(ReflectionClass $class): array;

}
