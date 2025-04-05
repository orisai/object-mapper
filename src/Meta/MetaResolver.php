<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Meta;

use Orisai\Exceptions\Logic\InvalidArgument;
use Orisai\Exceptions\Logic\InvalidState;
use Orisai\Exceptions\Message;
use Orisai\ObjectMapper\Args\Args;
use Orisai\ObjectMapper\Callbacks\Callback;
use Orisai\ObjectMapper\MappedObject;
use Orisai\ObjectMapper\Meta\Compile\CallbackCompileMeta;
use Orisai\ObjectMapper\Meta\Compile\ClassCompileMeta;
use Orisai\ObjectMapper\Meta\Compile\CompileMeta;
use Orisai\ObjectMapper\Meta\Compile\FieldCompileMeta;
use Orisai\ObjectMapper\Meta\Compile\ModifierCompileMeta;
use Orisai\ObjectMapper\Meta\Compile\NodeCompileMeta;
use Orisai\ObjectMapper\Meta\Compile\RuleCompileMeta;
use Orisai\ObjectMapper\Meta\Context\MetaContext;
use Orisai\ObjectMapper\Meta\Context\MetaFieldContext;
use Orisai\ObjectMapper\Meta\Runtime\CallbackRuntimeMeta;
use Orisai\ObjectMapper\Meta\Runtime\ClassRuntimeMeta;
use Orisai\ObjectMapper\Meta\Runtime\FieldRuntimeMeta;
use Orisai\ObjectMapper\Meta\Runtime\ModifierRuntimeMeta;
use Orisai\ObjectMapper\Meta\Runtime\PhpPropertyMeta;
use Orisai\ObjectMapper\Meta\Runtime\RuleRuntimeMeta;
use Orisai\ObjectMapper\Meta\Runtime\RuntimeMeta;
use Orisai\ObjectMapper\Meta\Shared\DefaultValueMeta;
use Orisai\ObjectMapper\Meta\Shared\DocMeta;
use Orisai\ObjectMapper\Modifiers\DefaultValueModifier;
use Orisai\ObjectMapper\Modifiers\FieldNameModifier;
use Orisai\ObjectMapper\Modifiers\Modifier;
use Orisai\ObjectMapper\Modifiers\RequiresDependenciesModifier;
use Orisai\ObjectMapper\Processing\ObjectCreator;
use Orisai\ObjectMapper\Rules\RuleManager;
use Orisai\ReflectionMeta\Structure\PropertyStructure;
use Orisai\SourceMap\ClassSource;
use Orisai\SourceMap\PropertySource;
use ReflectionClass;
use ReflectionProperty;
use Reflector;
use function array_key_exists;
use function array_merge;
use function assert;
use function get_class;
use function is_a;
use function is_int;
use function is_string;
use const PHP_VERSION_ID;

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
		$this->checkFieldNames($class, $meta);

		$runtimeMeta = new RuntimeMeta(
			$this->resolveClassMeta($class, $meta),
			$this->resolveFieldsMeta($class, $meta),
		);

		$this->checkObjectCanBeInstantiated($class, $runtimeMeta->class);

		return $runtimeMeta;
	}

	/**
	 * @param ReflectionClass<MappedObject> $rootClass
	 */
	private function resolveClassMeta(ReflectionClass $rootClass, CompileMeta $meta): ClassRuntimeMeta
	{
		$callbacksByMeta = [];
		$docsByMeta = [];
		$modifiersByMeta = [];

		foreach ($meta->getClasses() as $classMeta) {
			$classStructure = $classMeta->getClass();
			$reflector = $classStructure->getContextReflector();

			$source = $classStructure->getSource();
			$sourceReflector = $source->getReflector();
			if (
				!$reflector->isSubclassOf(MappedObject::class)
				|| ($sourceReflector->isInterface() && !$sourceReflector->isSubclassOf(MappedObject::class))
			) {
				$this->throwClassMetaOutsideOfMappedObject(
					$rootClass,
					$classStructure->getContextReflector(),
					$source,
				);
			}

			$context = new MetaContext($this->loader, $this);

			$callbacksByMeta[] = $this->resolveCallbacksMeta($classMeta, $context, $reflector);
			$docsByMeta[] = $this->resolveDocsMeta($classMeta, $context);
			$modifiersByMeta[] = $this->resolveClassModifiersMeta($classMeta, $context);
		}

		$modifiers = [];
		foreach ($modifiersByMeta as $value) {
			foreach ($value as $modifierClass => $modifierMetas) {
				foreach ($modifierMetas as $modifierMeta) {
					$modifiers[$modifierClass][] = $modifierMeta;
				}
			}
		}

		return new ClassRuntimeMeta(
			array_merge(...$callbacksByMeta),
			array_merge(...$docsByMeta),
			$modifiers,
		);
	}

	/**
	 * @param ReflectionClass<covariant MappedObject> $rootClass
	 * @param ReflectionClass<covariant object> $reflector
	 * @return never
	 */
	private function throwClassMetaOutsideOfMappedObject(
		ReflectionClass $rootClass,
		ReflectionClass $reflector,
		ClassSource $source
	): void
	{
		$sourceReflector = $source->getReflector();

		$objectInterface = MappedObject::class;
		$actionName = $sourceReflector->isInterface()
			? 'Extend'
			: 'Implement';
		$message = Message::create()
			->withContext("Resolving metadata of mapped object '{$rootClass->getName()}'.")
			->withSolution("$actionName the '$objectInterface' interface.");

		if ($sourceReflector->isTrait()) {
			$message->withProblem(
				"Trait '{$source->toString()}' defines metadata, but is used in class"
				. " '{$reflector->getName()}' which does not implement mapped object.",
			);
		} elseif ($sourceReflector->isInterface()) {
			$message->withProblem(
				"Interface '{$source->toString()}' defines metadata,"
				. ' but does not extend mapped object.',
			);
		} else {
			$message->withProblem(
				"Class '{$source->toString()}' defines metadata,"
				. ' but does not implement mapped object.',
			);
		}

		throw InvalidArgument::create()
			->withMessage($message);
	}

	/**
	 * @param ReflectionClass<MappedObject> $class
	 */
	private function checkObjectCanBeInstantiated(ReflectionClass $class, ClassRuntimeMeta $meta): void
	{
		$injectors = [];
		foreach ($meta->getModifier(RequiresDependenciesModifier::class) as $modifier) {
			$injectors[] = $modifier->args->injector;
		}

		$this->objectCreator->createInstance($class->getName(), $injectors);
	}

	/**
	 * @param ReflectionClass<MappedObject> $rootClass
	 * @return array<int|string, FieldRuntimeMeta>
	 */
	private function resolveFieldsMeta(ReflectionClass $rootClass, CompileMeta $meta): array
	{
		$fields = [];
		foreach ($meta->getFields() as $fieldMetas) {
			foreach ($fieldMetas as $fieldMeta) {
				$resolved = $this->resolveFieldMeta(
					$rootClass,
					$fieldMeta,
					$this->getDefaultValue($fieldMeta),
				);

				$fieldName = $this->propertyNameToFieldName($resolved);
				$fields[$fieldName] = $resolved;
			}
		}

		return $fields;
	}

	/**
	 * @return int|string
	 */
	private function propertyNameToFieldName(FieldRuntimeMeta $fieldMeta)
	{
		$modifier = $fieldMeta->getModifier(FieldNameModifier::class);
		if ($modifier !== null) {
			return $modifier->args->name;
		}

		return $fieldMeta->property->name;
	}

	/**
	 * @param ReflectionClass<MappedObject> $rootClass
	 */
	private function resolveFieldMeta(
		ReflectionClass $rootClass,
		FieldCompileMeta $meta,
		DefaultValueMeta $defaultValue
	): FieldRuntimeMeta
	{
		$fieldStructure = $meta->getProperty();
		$reflector = $fieldStructure->getContextReflector();

		if ($reflector->isStatic()) {
			$message = Message::create()
				->withContext("Resolving metadata of mapped object '{$rootClass->getName()}'.")
				->withProblem(
					"Mapped property {$fieldStructure->getSource()->toString()} is static, but static properties are not supported.",
				)
				->withSolution('Make the property non-static.');

			throw InvalidArgument::create()
				->withMessage($message);
		}

		$classReflector = $reflector->getDeclaringClass();
		if (!$classReflector->isSubclassOf(MappedObject::class)) {
			$this->throwFieldMetaOutsideOfMappedObject($rootClass, $classReflector, $fieldStructure->getSource());
		}

		$context = new MetaFieldContext($this->loader, $this, $defaultValue);

		return new FieldRuntimeMeta(
			$this->resolveCallbacksMeta($meta, $context, $reflector),
			$this->resolveDocsMeta($meta, $context),
			$this->resolveFieldModifiersMeta($meta, $context),
			$this->resolveRuleMeta(
				$meta->getRule(),
				$context,
			),
			$defaultValue,
			PhpPropertyMeta::from($reflector),
		);
	}

	/**
	 * @param ReflectionClass<MappedObject> $rootClass
	 * @param ReflectionClass<object> $classReflector
	 * @return never
	 */
	private function throwFieldMetaOutsideOfMappedObject(
		ReflectionClass $rootClass,
		ReflectionClass $classReflector,
		PropertySource $source
	): void
	{
		$objectInterface = MappedObject::class;
		$message = Message::create()
			->withContext("Resolving metadata of mapped object '{$rootClass->getName()}'.")
			->withSolution("Implement the '$objectInterface' interface.");

		if ($source->getReflector()->getDeclaringClass()->isTrait()) {
			$message->withProblem(
				"Property '{$source->toString()}' defines metadata, but its trait is used in class"
				. " '{$classReflector->getName()}' which does not implement mapped object.",
			);
		} else {
			$message->withProblem(
				"Property '{$source->toString()}' defines metadata,"
				. " but the class '{$classReflector->getName()}' does not implement mapped object.",
			);
		}

		throw InvalidArgument::create()
			->withMessage($message);
	}

	/**
	 * @param ReflectionClass<MappedObject>|ReflectionProperty $reflector
	 * @return array<class-string<Callback<Args>>, list<CallbackRuntimeMeta<Args>>>
	 */
	private function resolveCallbacksMeta(
		NodeCompileMeta $meta,
		MetaContext $context,
		Reflector $reflector
	): array
	{
		$array = [];
		foreach ($meta->getCallbacks() as $callback) {
			$callbackMeta = $this->resolveCallbackMeta(
				$callback,
				$context,
				$reflector,
			);

			$array[$callbackMeta->type][] = $callbackMeta;
		}

		return $array;
	}

	/**
	 * @param ReflectionClass<MappedObject>|ReflectionProperty $reflector
	 * @return CallbackRuntimeMeta<Args>
	 */
	private function resolveCallbackMeta(
		CallbackCompileMeta $meta,
		MetaContext $context,
		Reflector $reflector
	): CallbackRuntimeMeta
	{
		$type = $meta->getType();
		$args = $type::resolveArgs($meta->getArgs(), $context, $reflector);

		$argsType = $type::getArgsType();
		if (!is_a($args, $argsType)) {
			$realArgsType = get_class($args);

			throw InvalidArgument::create()
				->withMessage(
					"'{$type}::resolveArgs()' should return '$argsType' (as defined in 'getArgsType()' method)"
					. ", but returns '$realArgsType'.",
				);
		}

		return new CallbackRuntimeMeta($type, $args);
	}

	/**
	 * @return array<string, DocMeta>
	 */
	private function resolveDocsMeta(NodeCompileMeta $meta, MetaContext $context): array
	{
		$array = [];
		foreach ($meta->getDocs() as $doc) {
			$array[$doc->getName()::getUniqueName()] = $this->resolveDocMeta($doc, $context);
		}

		return $array;
	}

	public function resolveDocMeta(DocMeta $meta, MetaContext $context): DocMeta
	{
		$type = $meta->getName();
		$args = $type::resolveArgs($meta->getArgs(), $context);

		return new DocMeta($type, $args);
	}

	/**
	 * @return array<class-string<Modifier<Args>>, list<ModifierRuntimeMeta<Args>>>
	 */
	private function resolveClassModifiersMeta(ClassCompileMeta $meta, MetaContext $context): array
	{
		$array = [];
		foreach ($meta->getModifiers() as $modifier) {
			$array[$modifier->getType()][] = $this->resolveModifierMeta($modifier, $context);
		}

		return $array;
	}

	/**
	 * @return array<class-string<Modifier<Args>>, ModifierRuntimeMeta<Args>>
	 */
	private function resolveFieldModifiersMeta(FieldCompileMeta $meta, MetaContext $context): array
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
	private function resolveModifierMeta(ModifierCompileMeta $meta, MetaContext $context): ModifierRuntimeMeta
	{
		$type = $meta->getType();
		$args = $type::resolveArgs($meta->getArgs(), $context);

		return new ModifierRuntimeMeta($type, $args);
	}

	/**
	 * @return RuleRuntimeMeta<Args>
	 */
	public function resolveRuleMeta(RuleCompileMeta $meta, MetaFieldContext $context): RuleRuntimeMeta
	{
		$type = $meta->getType();
		$rule = $this->ruleManager->getRule($type);
		$args = $rule->resolveArgs($meta->getArgs(), $context);

		$argsType = $rule->getArgsType();
		if (!is_a($args, $argsType)) {
			$ruleClass = get_class($rule);
			$realArgsType = get_class($args);

			throw InvalidArgument::create()
				->withMessage(
					"'{$ruleClass}->resolveArgs()' should return '$argsType' (as defined in 'getArgsType()' method)"
					. ", but returns '$realArgsType'.",
				);
		}

		return new RuleRuntimeMeta($type, $args);
	}

	private function getDefaultValue(FieldCompileMeta $meta): DefaultValueMeta
	{
		foreach ($meta->getModifiers() as $modifier) {
			if ($modifier->getType() === DefaultValueModifier::class) {
				return DefaultValueMeta::fromValue($modifier->getArgs()[DefaultValueModifier::Value]);
			}
		}

		$property = $meta->getProperty()->getContextReflector();
		$propertyName = $property->getName();
		$declaringClass = $property->getDeclaringClass();

		// Promoted property default value is accessible only via ctor parameter
		if (PHP_VERSION_ID >= 8_00_00 && $property->isPromoted()) {
			$ctor = $declaringClass->getMethod('__construct');
			foreach ($ctor->getParameters() as $parameter) {
				if ($parameter->getName() === $propertyName) {
					return $parameter->isOptional()
						? DefaultValueMeta::fromValue($parameter->getDefaultValue())
						: DefaultValueMeta::fromNothing();
				}
			}
		}

		// ReflectionProperty->getDefaultValue() is available since PHP 8.0, we support 7.4
		$defaults = $declaringClass->getDefaultProperties();
		if (!array_key_exists($propertyName, $defaults)) {
			return DefaultValueMeta::fromNothing();
		}

		$propertyValue = $defaults[$propertyName];

		// It's not possible to distinguish between null and uninitialized for properties without type,
		// and so we treat it as uninitialized. Use DefaultValue annotation for untyped null default.
		if ($propertyValue === null && !$property->hasType()) {
			return DefaultValueMeta::fromNothing();
		}

		return DefaultValueMeta::fromValue($propertyValue);
	}

	/**
	 * @param ReflectionClass<MappedObject> $rootClass
	 */
	private function checkFieldNames(ReflectionClass $rootClass, CompileMeta $meta): void
	{
		/** @var array<int|string, PropertyStructure> $map */
		$map = [];
		foreach ($meta->getFields() as $fieldMetas) {
			foreach ($fieldMetas as $fieldMeta) {
				$propertyStructure = $fieldMeta->getProperty();
				$property = $propertyStructure->getContextReflector();

				$fieldName = $property->getName();

				foreach ($fieldMeta->getModifiers() as $modifier) {
					if ($modifier->getType() === FieldNameModifier::class) {
						$fieldName = $modifier->getArgs()[FieldNameModifier::Name];
						assert(is_string($fieldName) || is_int($fieldName));

						break;
					}
				}

				$collidingPropertyStructure = $map[$fieldName] ?? null;
				if ($collidingPropertyStructure !== null) {
					$collidingProperty = $collidingPropertyStructure->getContextReflector();
					$isSameProperty = !$property->isPrivate()
						&& !$collidingProperty->isPrivate()
						&& $property->getName() === $collidingProperty->getName();

					if (!$isSameProperty) {
						$propertyName = $this->getRelativePropertyName(
							$propertyStructure,
							$rootClass,
						);
						$collidingPropertyName = $this->getRelativePropertyName(
							$collidingPropertyStructure,
							$rootClass,
						);

						$message = Message::create()
							->withContext("Resolving metadata of mapped object '{$rootClass->getName()}'.")
							->withProblem("Properties '$propertyName' and '$collidingPropertyName'"
								. " have conflicting field name '$fieldName'.")
							->withSolution('Define unique field name for each mapped property.');

						throw InvalidState::create()
							->withMessage($message);
					}
				}

				$map[$fieldName] = $propertyStructure;
			}
		}
	}

	/**
	 * @param ReflectionClass<MappedObject> $rootClass
	 */
	private function getRelativePropertyName(PropertyStructure $propertyStructure, ReflectionClass $rootClass): string
	{
		$property = $propertyStructure->getSource()->getReflector();
		$class = $property->getDeclaringClass();

		if ($class->getName() === $rootClass->getName()) {
			return '$' . $property->getName();
		}

		return $class->getName() . '->$' . $property->getName();
	}

}
