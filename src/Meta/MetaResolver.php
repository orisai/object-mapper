<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Meta;

use Orisai\Exceptions\Logic\InvalidArgument;
use Orisai\Exceptions\Logic\InvalidState;
use Orisai\Exceptions\Message;
use Orisai\ObjectMapper\Callbacks\Callback;
use Orisai\ObjectMapper\Context\ArgsContext;
use Orisai\ObjectMapper\Context\RuleArgsContext;
use Orisai\ObjectMapper\Docs\Doc;
use Orisai\ObjectMapper\Exception\InvalidMeta;
use Orisai\ObjectMapper\Modifiers\FieldNameModifier;
use Orisai\ObjectMapper\Modifiers\Modifier;
use Orisai\ObjectMapper\Rules\MixedRule;
use Orisai\ObjectMapper\Rules\NullRule;
use Orisai\ObjectMapper\Rules\Rule;
use Orisai\ObjectMapper\Rules\RuleManager;
use Orisai\ObjectMapper\ValueObject;
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
	 * @param ReflectionClass<ValueObject> $class
	 * @param array<mixed> $meta
	 * @return array<mixed>
	 */
	public function resolve(ReflectionClass $class, array $meta): array
	{
		$meta = $this->resolveMeta($meta, $class);
		$meta = $this->resolveDefaultValues($meta, $class);

		return $meta;
	}

	/**
	 * @param array<mixed> $meta
	 * @param ReflectionClass<ValueObject> $class
	 * @return array<mixed>
	 */
	private function resolveMeta(array $meta, ReflectionClass $class): array
	{
		if (array_key_exists(MetaSource::LOCATION_CLASS, $meta)) {
			if (!is_array($meta[MetaSource::LOCATION_CLASS])) {
				throw InvalidMeta::create();
			}

			$meta[MetaSource::LOCATION_CLASS] = $this->resolveClassMeta($meta[MetaSource::LOCATION_CLASS], $class);
		}

		if (array_key_exists(MetaSource::LOCATION_PROPERTIES, $meta)) {
			if (!is_array($meta[MetaSource::LOCATION_PROPERTIES])) {
				throw InvalidMeta::create();
			}

			$meta[MetaSource::LOCATION_PROPERTIES] = $this->resolvePropertiesMeta(
				$meta[MetaSource::LOCATION_PROPERTIES],
				$class,
			);

			$meta[self::FIELDS_PROPERTIES_MAP] = $this->resolveFieldsPropertiesMap(
				$meta[MetaSource::LOCATION_PROPERTIES],
				$class,
			);
		}

		return $meta;
	}

	/**
	 * Meta which are same for both classes and properties
	 *
	 * @param array<mixed> $meta
	 * @param ReflectionClass<ValueObject> $class
	 * @return array<mixed>
	 */
	private function resolveReflectorMeta(array $meta, ReflectionClass $class, ?ReflectionProperty $property): array
	{
		$context = $this->createArgsContext($class, $property);

		if (array_key_exists(MetaSource::TYPE_CALLBACKS, $meta)) {
			if (!is_array($meta[MetaSource::TYPE_CALLBACKS])) {
				throw InvalidMeta::create();
			}

			$meta[MetaSource::TYPE_CALLBACKS] = $this->resolveCallbacksMeta(
				$meta[MetaSource::TYPE_CALLBACKS],
				$context,
			);
		}

		if (array_key_exists(MetaSource::TYPE_DOCS, $meta)) {
			if (!is_array($meta[MetaSource::TYPE_DOCS])) {
				throw InvalidMeta::create();
			}

			$meta[MetaSource::TYPE_DOCS] = $this->resolveDocsMeta($meta[MetaSource::TYPE_DOCS], $context);
		}

		if (array_key_exists(MetaSource::TYPE_MODIFIERS, $meta)) {
			if (!is_array($meta[MetaSource::TYPE_MODIFIERS])) {
				throw InvalidMeta::create();
			}

			$meta[MetaSource::TYPE_MODIFIERS] = $this->resolveModifiersMeta(
				$meta[MetaSource::TYPE_MODIFIERS],
				$context,
			);
		}

		return $meta;
	}

	/**
	 * @param array<mixed> $meta
	 * @param ReflectionClass<ValueObject> $class
	 * @return array<mixed>
	 */
	private function resolveClassMeta(array $meta, ReflectionClass $class): array
	{
		if (array_key_exists(MetaSource::TYPE_RULE, $meta)) {
			throw InvalidArgument::create()
				->withMessage('Rules cannot be used for class, only properties are allowed');
		}

		return $this->resolveReflectorMeta($meta, $class, null);
	}

	/**
	 * @param array<mixed> $meta
	 * @param ReflectionClass<ValueObject> $class
	 * @return array<mixed>
	 */
	private function resolvePropertiesMeta(array $meta, ReflectionClass $class): array
	{
		foreach ($meta as $propertyName => $propertyMeta) {
			$property = $class->getProperty($propertyName);

			if (!is_array($propertyMeta)) {
				throw InvalidMeta::create();
			}

			if (!isset($propertyMeta[MetaSource::TYPE_RULE])) {
				throw InvalidArgument::create()
					->withMessage(sprintf(
						'Property %s::$%s requires a rule, standalone usage of documentation and callbacks is not allowed for mapped properties',
						$class->getName(),
						$property->getName(),
					));
			}

			$meta[$propertyName] = $this->resolvePropertyMeta($propertyMeta, $class, $property);
		}

		return $meta;
	}

	/**
	 * @param array<mixed> $meta
	 * @param ReflectionClass<ValueObject> $class
	 * @return array<mixed>
	 */
	private function resolvePropertyMeta(array $meta, ReflectionClass $class, ReflectionProperty $property): array
	{
		if (!$property->isPublic() || $property->isStatic()) {
			throw InvalidArgument::create()
				->withMessage(sprintf(
					'Property %s::$%s is not valid mapped property, \'%s\' supports only non-static public properties to be mapped.',
					$class->getName(),
					$property->getName(),
					ValueObject::class,
				));
		}

		$meta = $this->resolveReflectorMeta($meta, $class, $property);

		if (!array_key_exists(MetaSource::TYPE_RULE, $meta) || !is_array($meta[MetaSource::TYPE_RULE])) {
			throw InvalidMeta::create();
		}

		$meta[MetaSource::TYPE_RULE] = $this->resolveRuleMeta(
			$meta[MetaSource::TYPE_RULE],
			$this->createRuleArgsContext($class, $property),
		);

		return $meta;
	}

	/**
	 * @param array<mixed> $meta
	 * @return array<mixed>
	 */
	private function resolveCallbacksMeta(array $meta, ArgsContext $context): array
	{
		foreach ($meta as $key => $callback) {
			if (!is_array($callback)) {
				throw InvalidMeta::create();
			}

			$meta[$key] = $this->resolveCallbackMeta($callback, $context);
		}

		return $meta;
	}

	/**
	 * @param array<mixed> $meta
	 * @return array<mixed>
	 */
	private function resolveCallbackMeta(array $meta, ArgsContext $context): array
	{
		if (
			!array_key_exists(MetaSource::OPTION_TYPE, $meta)
			|| !is_string(($type = $meta[MetaSource::OPTION_TYPE]))
			|| !is_subclass_of($type, Callback::class)
		) {
			throw InvalidMeta::create();
		}

		if (array_key_exists(MetaSource::OPTION_ARGS, $meta)) {
			$args = $meta[MetaSource::OPTION_ARGS];

			if (!is_array($args)) {
				throw InvalidMeta::create();
			}
		} else {
			$args = [];
		}

		$meta[MetaSource::OPTION_ARGS] = $type::resolveArgs($args, $context);

		return $meta;
	}

	/**
	 * @param array<mixed> $meta
	 * @return array<mixed>
	 */
	public function resolveDocsMeta(array $meta, ArgsContext $context): array
	{
		$optimized = [];

		foreach ($meta as $doc) {
			if (!is_array($doc)) {
				throw InvalidMeta::create();
			}

			[, $name, $args] = $this->resolveDocMeta($doc, $context);
			$optimized[$name] = $args;
		}

		return $optimized;
	}

	/**
	 * @param array<mixed> $meta
	 * @return array<mixed>
	 */
	public function resolveDocMeta(array $meta, ArgsContext $context): array
	{
		if (
			!array_key_exists(MetaSource::OPTION_TYPE, $meta)
			|| !is_string(($type = $meta[MetaSource::OPTION_TYPE]))
			|| !is_subclass_of($type, Doc::class)
		) {
			throw InvalidMeta::create();
		}

		if (array_key_exists(MetaSource::OPTION_ARGS, $meta)) {
			$args = $meta[MetaSource::OPTION_ARGS];

			if (!is_array($args)) {
				throw InvalidMeta::create();
			}
		} else {
			$args = [];
		}

		$name = $type::getUniqueName();
		$args = $type::resolveArgs($args, $context);

		return [$type, $name, $args];
	}

	/**
	 * @param array<mixed> $meta
	 * @return array<mixed>
	 */
	private function resolveModifiersMeta(array $meta, ArgsContext $context): array
	{
		$optimized = [];

		foreach ($meta as $modifier) {
			if (!is_array($modifier)) {
				throw InvalidMeta::create();
			}

			[$type, $args] = $this->resolveModifierMeta($modifier, $context);
			$optimized[$type] = $args;
		}

		return $optimized;
	}

	/**
	 * @param array<mixed> $meta
	 * @return array<mixed>
	 */
	private function resolveModifierMeta(array $meta, ArgsContext $context): array
	{
		if (
			!array_key_exists(MetaSource::OPTION_TYPE, $meta)
			|| !is_string(($type = $meta[MetaSource::OPTION_TYPE]))
			|| !is_subclass_of($type, Modifier::class)
		) {
			throw InvalidMeta::create();
		}

		if (array_key_exists(MetaSource::OPTION_ARGS, $meta)) {
			$args = $meta[MetaSource::OPTION_ARGS];

			if (!is_array($args)) {
				throw InvalidMeta::create();
			}
		} else {
			$args = [];
		}

		$args = $type::resolveArgs($args, $context);

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

		if (array_key_exists(MetaSource::OPTION_ARGS, $meta)) {
			if (!is_array($meta[MetaSource::OPTION_ARGS])) {
				throw InvalidMeta::create();
			}

			$meta[MetaSource::OPTION_ARGS] = $rule->resolveArgs($meta[MetaSource::OPTION_ARGS], $context);
		}

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

		return $meta;
	}

	/**
	 * @param array<mixed> $meta
	 * @param ReflectionClass<ValueObject> $class
	 * @return array<mixed>
	 */
	private function resolveDefaultValues(array $meta, ReflectionClass $class): array
	{
		foreach ($class->getDefaultProperties() as $propertyName => $propertyValue) {
			// Property is not mapped property
			if (!isset($meta[MetaSource::LOCATION_PROPERTIES][$propertyName])) {
				continue;
			}

			$isPropertyTyped = $class->getProperty($propertyName)->hasType();

			// It's not possible to distinguish between null and uninitialized for properties without type,
			// default null is used only if validation allows null
			if (
				$propertyValue === null
				&& !$isPropertyTyped
				&& !PropertyMeta::fromArray(
					$meta[MetaSource::LOCATION_PROPERTIES][$propertyName],
				)->getRule()->mayContainRuleType(
					[NullRule::class, MixedRule::class],
				)
			) {
				continue;
			}

			$meta[MetaSource::LOCATION_PROPERTIES][$propertyName][MetaSource::TYPE_DEFAULT_VALUE] = $propertyValue;
		}

		return $meta;
	}

	/**
	 * @param array<mixed> $properties
	 * @param ReflectionClass<ValueObject> $class
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
	 * @param ReflectionClass<ValueObject> $class
	 */
	private function createArgsContext(ReflectionClass $class, ?ReflectionProperty $property): ArgsContext
	{
		return new ArgsContext($class, $property, $this);
	}

	/**
	 * @param ReflectionClass<ValueObject> $class
	 */
	private function createRuleArgsContext(ReflectionClass $class, ReflectionProperty $property): RuleArgsContext
	{
		return new RuleArgsContext($class, $property, $this->ruleManager, $this->loader, $this);
	}

}
