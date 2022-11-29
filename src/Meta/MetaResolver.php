<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Meta;

use Orisai\Exceptions\Logic\InvalidArgument;
use Orisai\Exceptions\Logic\InvalidState;
use Orisai\Exceptions\Message;
use Orisai\ObjectMapper\Args\Args;
use Orisai\ObjectMapper\Context\ResolverArgsContext;
use Orisai\ObjectMapper\Context\RuleArgsContext;
use Orisai\ObjectMapper\MappedObject;
use Orisai\ObjectMapper\Meta\Compile\CallbackCompileMeta;
use Orisai\ObjectMapper\Meta\Compile\CompileMeta;
use Orisai\ObjectMapper\Meta\Compile\ModifierCompileMeta;
use Orisai\ObjectMapper\Meta\Compile\NodeCompileMeta;
use Orisai\ObjectMapper\Meta\Compile\PropertyCompileMeta;
use Orisai\ObjectMapper\Meta\Compile\RuleCompileMeta;
use Orisai\ObjectMapper\Meta\Runtime\CallbackRuntimeMeta;
use Orisai\ObjectMapper\Meta\Runtime\ClassRuntimeMeta;
use Orisai\ObjectMapper\Meta\Runtime\ModifierRuntimeMeta;
use Orisai\ObjectMapper\Meta\Runtime\PropertyRuntimeMeta;
use Orisai\ObjectMapper\Meta\Runtime\RuleRuntimeMeta;
use Orisai\ObjectMapper\Meta\Runtime\RuntimeMeta;
use Orisai\ObjectMapper\Modifiers\CreateWithoutConstructorModifier;
use Orisai\ObjectMapper\Modifiers\FieldNameModifier;
use Orisai\ObjectMapper\Modifiers\Modifier;
use Orisai\ObjectMapper\Processing\ObjectCreator;
use Orisai\ObjectMapper\Rules\MixedRule;
use Orisai\ObjectMapper\Rules\NullRule;
use Orisai\ObjectMapper\Rules\RuleManager;
use ReflectionClass;
use ReflectionProperty;
use function array_key_exists;
use function class_exists;
use function get_class;
use function implode;
use function in_array;
use function sprintf;

/**
 * Validate meta and resolve context-specific arguments
 */
final class MetaResolver
{

	private MetaLoader $loader;

	private RuleManager $ruleManager;

	private ObjectCreator $objectCreator;

	public function __construct(MetaLoader $loader, RuleManager $ruleManager, ObjectCreator $objectCreator)
	{
		$this->loader = $loader;
		$this->ruleManager = $ruleManager;
		$this->objectCreator = $objectCreator;
	}

	/**
	 * @param ReflectionClass<MappedObject> $class
	 */
	public function resolve(ReflectionClass $class, CompileMeta $meta): RuntimeMeta
	{
		return new RuntimeMeta(
			$this->resolveClassMeta($meta, $class),
			$this->resolvePropertiesMeta($meta, $class),
			$this->resolveFieldsPropertiesMap($meta, $class),
		);
	}

	/**
	 * @param ReflectionClass<MappedObject> $class
	 */
	private function resolveClassMeta(CompileMeta $meta, ReflectionClass $class): ClassRuntimeMeta
	{
		$context = ResolverArgsContext::forClass($class, $this);
		$classMeta = $meta->getClass();

		$runtimeMeta = new ClassRuntimeMeta(
			$this->resolveCallbacksMeta($classMeta, $context),
			$this->resolveDocsMeta($classMeta, $context),
			$this->resolveModifiersMeta($classMeta, $context),
		);

		$this->checkObjectCanBeInstantiated($class, $runtimeMeta);

		return $runtimeMeta;
	}

	/**
	 * @param ReflectionClass<MappedObject> $class
	 */
	private function checkObjectCanBeInstantiated(ReflectionClass $class, ClassRuntimeMeta $meta): void
	{
		$this->objectCreator->createInstance(
			$class->getName(),
			$meta->getModifier(CreateWithoutConstructorModifier::class) === null,
		);
	}

	/**
	 * @param ReflectionClass<MappedObject> $class
	 * @return array<string, PropertyRuntimeMeta>
	 */
	private function resolvePropertiesMeta(CompileMeta $meta, ReflectionClass $class): array
	{
		$defaults = $this->resolveDefaultValues($meta, $class);

		$array = [];
		foreach ($meta->getProperties() as $propertyName => $propertyMeta) {
			$property = $class->getProperty($propertyName);
			$array[$propertyName] = $this->resolvePropertyMeta(
				$propertyMeta,
				$property,
				$defaults[$propertyName] ?? DefaultValueMeta::fromNothing(),
			);
		}

		return $array;
	}

	private function resolvePropertyMeta(
		PropertyCompileMeta $meta,
		ReflectionProperty $property,
		DefaultValueMeta $defaultValue
	): PropertyRuntimeMeta
	{
		if ($property->isStatic()) {
			throw InvalidArgument::create()
				->withMessage(sprintf(
					'Property %s::$%s is not valid mapped property, \'%s\' supports only non-static properties to be mapped.',
					$property->getDeclaringClass()->getName(),
					$property->getName(),
					MappedObject::class,
				));
		}

		if ($property->isPrivate() && !$property->getDeclaringClass()->isFinal()) {
			throw InvalidArgument::create()
				->withMessage(sprintf(
					'Property %s::$%s is not valid mapped property, \'%s\' supports private properties to be mapped only in final classes.',
					$property->getDeclaringClass()->getName(),
					$property->getName(),
					MappedObject::class,
				));
		}

		$context = ResolverArgsContext::forProperty($property, $this);

		return new PropertyRuntimeMeta(
			$this->resolveCallbacksMeta($meta, $context),
			$this->resolveDocsMeta($meta, $context),
			$this->resolveModifiersMeta($meta, $context),
			$this->resolveRuleMeta(
				$meta->getRule(),
				$this->createRuleArgsContext($property),
			),
			$defaultValue,
		);
	}

	/**
	 * @return array<int, CallbackRuntimeMeta<Args>>
	 */
	private function resolveCallbacksMeta(NodeCompileMeta $meta, ResolverArgsContext $context): array
	{
		$array = [];
		foreach ($meta->getCallbacks() as $key => $callback) {
			$array[$key] = $this->resolveCallbackMeta($callback, $context);
		}

		return $array;
	}

	/**
	 * @return CallbackRuntimeMeta<Args>
	 */
	private function resolveCallbackMeta(CallbackCompileMeta $meta, ResolverArgsContext $context): CallbackRuntimeMeta
	{
		$type = $meta->getType();

		return new CallbackRuntimeMeta(
			$type,
			$type::resolveArgs($meta->getArgs(), $context),
		);
	}

	/**
	 * @return array<string, DocMeta>
	 */
	private function resolveDocsMeta(NodeCompileMeta $meta, ResolverArgsContext $context): array
	{
		$array = [];
		foreach ($meta->getDocs() as $doc) {
			$array[$doc->getName()::getUniqueName()] = $this->resolveDocMeta($doc, $context);
		}

		return $array;
	}

	public function resolveDocMeta(DocMeta $meta, ResolverArgsContext $context): DocMeta
	{
		$type = $meta->getName();
		$args = $type::resolveArgs($meta->getArgs(), $context);

		return new DocMeta($type, $args);
	}

	/**
	 * @return array<class-string<Modifier<Args>>, ModifierRuntimeMeta<Args>>
	 */
	private function resolveModifiersMeta(NodeCompileMeta $meta, ResolverArgsContext $context): array
	{
		$array = [];
		foreach ($meta->getModifiers() as $modifier) {
			$array[$modifier->getType()] = $this->resolveModifierMeta($modifier, $context);
		}

		return $array;
	}

	/**
	 * @return ModifierRuntimeMeta<Args>
	 */
	private function resolveModifierMeta(ModifierCompileMeta $meta, ResolverArgsContext $context): ModifierRuntimeMeta
	{
		$type = $meta->getType();
		$args = $type::resolveArgs($meta->getArgs(), $context);

		return new ModifierRuntimeMeta($type, $args);
	}

	/**
	 * @return RuleRuntimeMeta<Args>
	 */
	public function resolveRuleMeta(RuleCompileMeta $meta, RuleArgsContext $context): RuleRuntimeMeta
	{
		$type = $meta->getType();
		$rule = $this->ruleManager->getRule($type);
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

		return new RuleRuntimeMeta($type, $args);
	}

	/**
	 * @param ReflectionClass<MappedObject> $class
	 * @return array<string, DefaultValueMeta>
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
			$containsNullable = $properties[$propertyName]->getRule()->containsAnyOfRules(
				[NullRule::class, MixedRule::class],
			);

			// It's not possible to distinguish between null and uninitialized for properties without type,
			// default null is used only if validation allows null
			$propertiesMeta[$propertyName] = $propertyValue === null && !$isPropertyTyped && !$containsNullable
				? DefaultValueMeta::fromNothing()
				: DefaultValueMeta::fromValue($propertyValue);
		}

		return $propertiesMeta;
	}

	/**
	 * @param ReflectionClass<MappedObject> $class
	 * @return array<int|string, string>
	 */
	private function resolveFieldsPropertiesMap(CompileMeta $meta, ReflectionClass $class): array
	{
		$properties = $meta->getProperties();

		$map = [];
		foreach ($properties as $propertyName => $property) {
			$fieldNameMeta = null;
			foreach ($property->getModifiers() as $modifier) {
				if ($modifier->getType() === FieldNameModifier::class) {
					$fieldNameMeta = $modifier;

					break;
				}
			}

			if ($fieldNameMeta !== null) {
				$fieldName = $fieldNameMeta->getArgs()[FieldNameModifier::Name];

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

	private function createRuleArgsContext(ReflectionProperty $property): RuleArgsContext
	{
		return new RuleArgsContext($property, $this->ruleManager, $this->loader, $this);
	}

}
