<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Meta;

use Orisai\Exceptions\Logic\InvalidArgument;
use Orisai\Exceptions\Logic\InvalidState;
use Orisai\Exceptions\Message;
use Orisai\ObjectMapper\Context\ArgsContext;
use Orisai\ObjectMapper\Context\RuleArgsContext;
use Orisai\ObjectMapper\Exception\InvalidMeta;
use Orisai\ObjectMapper\MappedObject;
use Orisai\ObjectMapper\Meta\Compile\ClassCompileMeta;
use Orisai\ObjectMapper\Meta\Compile\CompileMeta;
use Orisai\ObjectMapper\Meta\Compile\PropertyCompileMeta;
use Orisai\ObjectMapper\Meta\Compile\SharedNodeCompileMeta;
use Orisai\ObjectMapper\Modifiers\FieldNameModifier;
use Orisai\ObjectMapper\Rules\MixedRule;
use Orisai\ObjectMapper\Rules\NullRule;
use Orisai\ObjectMapper\Rules\Rule;
use Orisai\ObjectMapper\Rules\RuleManager;
use Orisai\Utils\Arrays\ArrayMerger;
use ReflectionClass;
use ReflectionProperty;
use function array_key_exists;
use function class_exists;
use function get_class;
use function implode;
use function in_array;
use function is_array;
use function is_string;
use function is_subclass_of;
use function sprintf;

/**
 * Validate meta and resolve context-specific arguments
 */
final class MetaResolver
{

	public const FIELDS_PROPERTIES_MAP = 'fields_properties_map';

	private MetaLoader $loader;

	private RuleManager $ruleManager;

	public function __construct(MetaLoader $loader, RuleManager $ruleManager)
	{
		$this->loader = $loader;
		$this->ruleManager = $ruleManager;
	}

	/**
	 * @param ReflectionClass<MappedObject> $class
	 * @return array<mixed>
	 */
	public function resolve(ReflectionClass $class, CompileMeta $meta): array
	{
		$array = $this->resolveMeta($meta, $class);
		$defaults = $this->resolveDefaultValues($meta, $class);

		return ArrayMerger::merge($array, $defaults);
	}

	/**
	 * @param ReflectionClass<MappedObject> $class
	 * @return array<mixed>
	 */
	private function resolveMeta(CompileMeta $meta, ReflectionClass $class): array
	{
		$array = [];

		$array[MetaSource::LOCATION_CLASS] = $this->resolveClassMeta($meta->getClass(), $class);

		$array[MetaSource::LOCATION_PROPERTIES] = $this->resolvePropertiesMeta(
			$meta->getProperties(),
			$class,
		);

		$array[self::FIELDS_PROPERTIES_MAP] = $this->resolveFieldsPropertiesMap(
			$array[MetaSource::LOCATION_PROPERTIES],
			$class,
		);

		return $array;
	}

	/**
	 * Meta which are same for both classes and properties
	 *
	 * @param ReflectionClass<MappedObject> $class
	 * @return array<mixed>
	 */
	private function resolveReflectorMeta(
		SharedNodeCompileMeta $meta,
		ReflectionClass $class,
		?ReflectionProperty $property
	): array
	{
		$context = $this->createArgsContext($class, $property);

		$array = [];

		$array[MetaSource::TYPE_CALLBACKS] = $this->resolveCallbacksMeta(
			$meta->getCallbacks(),
			$context,
		);

		$array[MetaSource::TYPE_DOCS] = $this->resolveDocsMeta($meta->getDocs(), $context);

		$array[MetaSource::TYPE_MODIFIERS] = $this->resolveModifiersMeta(
			$meta->getModifiers(),
			$context,
		);

		return $array;
	}

	/**
	 * @param ReflectionClass<MappedObject> $class
	 * @return array<mixed>
	 */
	private function resolveClassMeta(ClassCompileMeta $meta, ReflectionClass $class): array
	{
		return $this->resolveReflectorMeta($meta, $class, null);
	}

	/**
	 * @param array<string, PropertyCompileMeta> $meta
	 * @param ReflectionClass<MappedObject>      $class
	 * @return array<mixed>
	 */
	private function resolvePropertiesMeta(array $meta, ReflectionClass $class): array
	{
		$array = [];
		foreach ($meta as $propertyName => $propertyMeta) {
			$property = $class->getProperty($propertyName);

			$array[$propertyName] = $this->resolvePropertyMeta($propertyMeta, $class, $property);
		}

		return $array;
	}

	/**
	 * @param ReflectionClass<MappedObject> $class
	 * @return array<mixed>
	 */
	private function resolvePropertyMeta(
		PropertyCompileMeta $meta,
		ReflectionClass $class,
		ReflectionProperty $property
	): array
	{
		if (!$property->isPublic() || $property->isStatic()) {
			throw InvalidArgument::create()
				->withMessage(sprintf(
					'Property %s::$%s is not valid mapped property, \'%s\' supports only non-static public properties to be mapped.',
					$class->getName(),
					$property->getName(),
					MappedObject::class,
				));
		}

		$array = $this->resolveReflectorMeta($meta, $class, $property);

		$array[MetaSource::TYPE_RULE] = $this->resolveRuleMetaInternal(
			$meta->getRule(),
			$this->createRuleArgsContext($class, $property),
		);

		return $array;
	}

	/**
	 * @param array<int, CallbackMeta> $meta
	 * @return array<mixed>
	 */
	private function resolveCallbacksMeta(array $meta, ArgsContext $context): array
	{
		$array = [];
		foreach ($meta as $key => $callback) {
			$array[$key] = $this->resolveCallbackMeta($callback, $context);
		}

		return $array;
	}

	/**
	 * @return array<mixed>
	 */
	private function resolveCallbackMeta(CallbackMeta $meta, ArgsContext $context): array
	{
		$array = $meta->toArray();
		$array[MetaSource::OPTION_ARGS] = $meta->getType()::resolveArgs($meta->getArgs(), $context);

		return $array;
	}

	/**
	 * @param array<int, DocMeta> $meta
	 * @return array<mixed>
	 */
	public function resolveDocsMeta(array $meta, ArgsContext $context): array
	{
		$optimized = [];

		foreach ($meta as $doc) {
			[, $name, $args] = $this->resolveDocMeta($doc, $context);
			$optimized[$name] = $args;
		}

		return $optimized;
	}

	/**
	 * @return array<mixed>
	 */
	public function resolveDocMeta(DocMeta $meta, ArgsContext $context): array
	{
		$type = $meta->getName();
		$name = $type::getUniqueName();
		$args = $type::resolveArgs($meta->getArgs(), $context);

		return [$type, $name, $args];
	}

	/**
	 * @param array<int, ModifierMeta> $meta
	 * @return array<mixed>
	 */
	private function resolveModifiersMeta(array $meta, ArgsContext $context): array
	{
		$optimized = [];

		foreach ($meta as $modifier) {
			[$type, $args] = $this->resolveModifierMeta($modifier, $context);
			$optimized[$type] = $args;
		}

		return $optimized;
	}

	/**
	 * @return array<mixed>
	 */
	private function resolveModifierMeta(ModifierMeta $meta, ArgsContext $context): array
	{
		$type = $meta->getType();
		$args = $type::resolveArgs($meta->getArgs(), $context);

		return [$type, $args];
	}

	/**
	 * @param array<mixed> $meta
	 * @return array<mixed>
	 */
	public function resolveRuleMeta(array $meta, RuleArgsContext $context): array
	{
		if (
			!array_key_exists(MetaSource::OPTION_TYPE, $meta)
			|| !is_string($meta[MetaSource::OPTION_TYPE])
			|| !is_subclass_of($meta[MetaSource::OPTION_TYPE], Rule::class)
		) {
			throw InvalidMeta::create();
		}

		$rule = $this->ruleManager->getRule($meta[MetaSource::OPTION_TYPE]);

		if (!array_key_exists(MetaSource::OPTION_ARGS, $meta)) {
			$meta[MetaSource::OPTION_ARGS] = [];
		}

		if (!is_array($meta[MetaSource::OPTION_ARGS])) {
			throw InvalidMeta::create();
		}

		$meta[MetaSource::OPTION_ARGS] = $rule->resolveArgs($meta[MetaSource::OPTION_ARGS], $context);

		return $this->resolveRuleMetaInternal(
			new RuleMeta($meta[MetaSource::OPTION_TYPE], $meta[MetaSource::OPTION_ARGS]),
			$context,
		);
	}

	/**
	 * @return array<mixed>
	 */
	private function resolveRuleMetaInternal(RuleMeta $meta, RuleArgsContext $context): array
	{
		$rule = $this->ruleManager->getRule($meta->getType());
		$args = $rule->resolveArgs($meta->getArgs(), $context);

		$argsType = $rule->getArgsType();

		if (!class_exists($argsType)) {
			throw InvalidArgument::create()
				->withMessage(sprintf(
					'Class %s returned by %s::getArgsType() does not exist',
					$argsType,
					get_class($rule),
				));
		}

		$argsRef = new ReflectionClass($argsType);

		if ($argsRef->isAbstract() || $argsRef->isInterface() || $argsRef->isTrait()) {
			$ruleClass = get_class($rule);

			throw InvalidArgument::create()
				->withMessage("Class $argsType returned by $ruleClass::getArgsType() must be instantiable.");
		}

		$array = $meta->toArray();
		$array[MetaSource::OPTION_ARGS] = $args;

		return $array;
	}

	/**
	 * @param ReflectionClass<MappedObject> $class
	 * @return array<mixed>
	 */
	private function resolveDefaultValues(CompileMeta $meta, ReflectionClass $class): array
	{
		$propertiesMeta = [];
		$properties = $meta->getProperties();
		foreach ($class->getDefaultProperties() as $propertyName => $propertyValue) {
			// Property is not mapped property
			if (!isset($properties[$propertyName])) {
				continue;
			}

			$isPropertyTyped = $class->getProperty($propertyName)->hasType();

			// It's not possible to distinguish between null and uninitialized for properties without type,
			// default null is used only if validation allows null
			if (
				$propertyValue === null
				&& !$isPropertyTyped
				&& !PropertyMeta::fromArray(
					[
						MetaSource::TYPE_RULE => $properties[$propertyName]->getRule()->toArray(),
					],
				)->getRule()->mayContainRuleType(
					[NullRule::class, MixedRule::class],
				)
			) {
				continue;
			}

			$propertiesMeta[$propertyName][MetaSource::TYPE_DEFAULT_VALUE] = $propertyValue;
		}

		$array = [];
		$array[MetaSource::LOCATION_PROPERTIES] = $propertiesMeta;

		return $array;
	}

	/**
	 * @param array<mixed>                  $properties
	 * @param ReflectionClass<MappedObject> $class
	 * @return array<int|string, string>
	 */
	private function resolveFieldsPropertiesMap(array $properties, ReflectionClass $class): array
	{
		$map = [];
		foreach ($properties as $propertyName => $property) {
			$propertyMeta = PropertyMeta::fromArray($property);
			$fieldNameMeta = $propertyMeta->getModifier(FieldNameModifier::class);

			if ($fieldNameMeta !== null) {
				$fieldName = $fieldNameMeta->getArgs()[FieldNameModifier::NAME];

				if (isset($map[$fieldName])) {
					$message = Message::create()
						->withContext(sprintf(
							'Trying to define field name for mapped property of `%s`.',
							$class->getName(),
						))
						->withProblem(sprintf(
							'Field name `%s` is identical for properties `%s`.',
							$fieldName,
							implode(', ', [$map[$fieldName], $propertyName]),
						))
						->withSolution('Define unique field name for each mapped property.');

					throw InvalidState::create()
						->withMessage($message);
				}

				$map[$fieldName] = $propertyName;
			}
		}

		foreach ($map as $fieldName => $propertyName) {
			if (array_key_exists($fieldName, $properties) && !in_array($fieldName, $map, true)) {
				$message = Message::create()
					->withContext(sprintf(
						'Trying to define field name for mapped property of `%s`.',
						$class->getName(),
					))
					->withProblem(sprintf(
						'Field name `%s` defined by property `%s` collides with property `%s` which does not have a field name.',
						$fieldName,
						$propertyName,
						$fieldName,
					))
					->withSolution(sprintf(
						'Rename field of property `%s` or rename property `%s` or give it a unique field name.',
						$propertyName,
						$fieldName,
					));

				throw InvalidState::create()
					->withMessage($message);
			}
		}

		return $map;
	}

	/**
	 * @param ReflectionClass<MappedObject> $class
	 */
	private function createArgsContext(ReflectionClass $class, ?ReflectionProperty $property): ArgsContext
	{
		return new ArgsContext($class, $property, $this);
	}

	/**
	 * @param ReflectionClass<MappedObject> $class
	 */
	private function createRuleArgsContext(ReflectionClass $class, ReflectionProperty $property): RuleArgsContext
	{
		return new RuleArgsContext($class, $property, $this->ruleManager, $this->loader, $this);
	}

}
