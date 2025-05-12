<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Meta;

use Orisai\Exceptions\Logic\InvalidArgument;
use Orisai\Exceptions\Logic\InvalidState;
use Orisai\Exceptions\Message;
use Orisai\ObjectMapper\Args\Args;
use Orisai\ObjectMapper\Callbacks\AfterMappingCallback;
use Orisai\ObjectMapper\Callbacks\AfterValidationCallback;
use Orisai\ObjectMapper\Callbacks\BeforeValidationCallback;
use Orisai\ObjectMapper\Callbacks\Callback;
use Orisai\ObjectMapper\Callbacks\CallbackDefinition;
use Orisai\ObjectMapper\Docs\DocDefinition;
use Orisai\ObjectMapper\MappedObject;
use Orisai\ObjectMapper\Meta\Compile\CompileMeta;
use Orisai\ObjectMapper\Meta\Compile\FieldCompileMeta;
use Orisai\ObjectMapper\Meta\Context\MetaContext;
use Orisai\ObjectMapper\Meta\Context\MetaFieldContext;
use Orisai\ObjectMapper\Meta\Runtime\CallbackRuntimeMeta;
use Orisai\ObjectMapper\Meta\Runtime\ClassRuntimeMeta;
use Orisai\ObjectMapper\Meta\Runtime\DocMeta;
use Orisai\ObjectMapper\Meta\Runtime\FieldRuntimeMeta;
use Orisai\ObjectMapper\Meta\Runtime\ModifierRuntimeMeta;
use Orisai\ObjectMapper\Meta\Runtime\PhpPropertyMeta;
use Orisai\ObjectMapper\Meta\Runtime\RuleRuntimeMeta;
use Orisai\ObjectMapper\Meta\Runtime\RuntimeMeta;
use Orisai\ObjectMapper\Meta\Scope\HierarchyPosition;
use Orisai\ObjectMapper\Meta\Scope\RepeatableBehavior;
use Orisai\ObjectMapper\Meta\Scope\ScopeConfig;
use Orisai\ObjectMapper\Meta\Scope\ScopeResolver;
use Orisai\ObjectMapper\Meta\Scope\Target;
use Orisai\ObjectMapper\Meta\Shared\DefaultValueMeta;
use Orisai\ObjectMapper\Modifiers\DefaultValueModifier;
use Orisai\ObjectMapper\Modifiers\FieldNameModifier;
use Orisai\ObjectMapper\Modifiers\Modifier;
use Orisai\ObjectMapper\Modifiers\ModifierDefinition;
use Orisai\ObjectMapper\Modifiers\RequiresDependenciesModifier;
use Orisai\ObjectMapper\Processing\ObjectCreator;
use Orisai\ObjectMapper\Rules\Rule;
use Orisai\ObjectMapper\Rules\RuleDefinition;
use Orisai\ObjectMapper\Rules\RuleManager;
use Orisai\ReflectionMeta\Structure\PropertyStructure;
use Orisai\SourceMap\ClassSource;
use Orisai\SourceMap\PropertySource;
use ReflectionClass;
use ReflectionProperty;
use Reflector;
use function array_key_exists;
use function array_key_last;
use function array_merge;
use function assert;
use function get_class;
use function is_a;
use function is_int;
use function is_string;
use function sprintf;
use const PHP_VERSION_ID;

/**
 * Validate meta and resolve context-specific arguments
 */
final class RuntimeResolver
{

	private MetaLoader $loader;

	private RuleManager $ruleManager;

	private ObjectCreator $objectCreator;

	private ScopeResolver $scopeResolver;

	public function __construct(MetaLoader $loader, RuleManager $ruleManager, ObjectCreator $objectCreator)
	{
		$this->loader = $loader;
		$this->ruleManager = $ruleManager;
		$this->objectCreator = $objectCreator;
		$this->scopeResolver = new ScopeResolver();

		//TODO - kontrolovat u scope, že patří ke správnému typu definice
		$this->scopeResolver->setScopeConfig(Rule::class, new ScopeConfig(
			true,
			RepeatableBehavior::noRepeat(),
			HierarchyPosition::firstType(),
			[
				Target::targetProperty(),
			],
		));
		$this->scopeResolver->setScopeConfig(BeforeValidationCallback::class, new ScopeConfig(
			false,
			RepeatableBehavior::merge(),
			HierarchyPosition::anywhere(),
			[
				Target::targetClass(),
				Target::targetProperty(),
			],
		));
		$this->scopeResolver->setScopeConfig(AfterValidationCallback::class, new ScopeConfig(
			false,
			RepeatableBehavior::merge(),
			HierarchyPosition::anywhere(),
			[
				Target::targetClass(),
				Target::targetProperty(),
			],
		));
		$this->scopeResolver->setScopeConfig(AfterMappingCallback::class, new ScopeConfig(
			false,
			RepeatableBehavior::merge(),
			HierarchyPosition::anywhere(),
			[
				Target::targetClass(),
				Target::targetProperty(),
			],
		));
		$this->scopeResolver->setScopeConfig(DefaultValueModifier::class, new ScopeConfig(
			false,
			RepeatableBehavior::override(),
			HierarchyPosition::anywhere(),
			[
				Target::targetProperty(),
			],
		));
		$this->scopeResolver->setScopeConfig(FieldNameModifier::class, new ScopeConfig(
			false,
			RepeatableBehavior::noRepeat(),
			HierarchyPosition::firstType(),
			[
				Target::targetProperty(),
			],
		));
		$this->scopeResolver->setScopeConfig(RequiresDependenciesModifier::class, new ScopeConfig(
			false,
			RepeatableBehavior::merge(),
			HierarchyPosition::anywhere(),
			[
				Target::targetClass(),
			],
		));
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
	 * @return never
	 */
	private function throwUnsupportedDefinitionType(MetaDefinition $definition): void
	{
		throw InvalidArgument::create()
			->withMessage(sprintf(
				"Definition '%s' (subtype of '%s') should implement '%s', '%s', '%s' or '%s'.",
				get_class($definition),
				MetaDefinition::class,
				CallbackDefinition::class,
				DocDefinition::class,
				ModifierDefinition::class,
				RuleDefinition::class,
			));
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

			$definitions = $classMeta->getDefinitions();
			if ($definitions === []) {
				continue;
			}

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

			$callbacks = $docs = $modifiers = [];
			foreach ($definitions as $definition) {
				if ($definition instanceof CallbackDefinition) {
					$callbacks[] = $definition;
				} elseif ($definition instanceof DocDefinition) {
					$docs[] = $definition;
				} elseif ($definition instanceof ModifierDefinition) {
					$modifiers[] = $definition;
				} elseif ($definition instanceof RuleDefinition) {
					$className = $reflector->getName();
					$isRootClass = $rootClass->getName() === $className;

					$message = Message::create()
						->withContext("Resolving metadata of '{$rootClass->getName()}'.")
						->withProblem(sprintf(
							"Rule definition '%s'%s cannot be used on class, it is only allowed on properties.",
							get_class($definition),
							$isRootClass ? '' : " (used above class '$className')",
						));

					throw InvalidArgument::create()
						->withMessage($message);
				} else {
					$this->throwUnsupportedDefinitionType($definition);
				}
			}

			$context = new MetaContext($this->loader, $this);

			$callbacksByMeta[] = $this->resolveCallbacksMeta($callbacks, $context, $reflector);
			$docsByMeta[] = $this->resolveDocsMeta($docs, $context);
			$modifiersByMeta[] = $this->resolveClassModifiersMeta($modifiers, $context);
		}

		$modifiers = [];
		foreach ($modifiersByMeta as $value) {
			foreach ($value as $modifierClass => $modifierMetas) {
				foreach ($modifierMetas as $modifierMeta) {
					$modifiers[$modifierClass][] = $modifierMeta;
				}
			}
		}

		//TODO - docs - currently unused
		unset($docsByMeta);
		//array_merge(...$docsByMeta);

		return new ClassRuntimeMeta(
			array_merge(...$callbacksByMeta),
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
			->withContext("Resolving metadata of '{$rootClass->getName()}'.")
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
		foreach ($this->scopeResolver->resolveProperties($meta) as $propertyName => $scopedProperty) {
			$lastPropertyConfig = $scopedProperty[array_key_last($scopedProperty)];
			//TODO - z těch ostatních se mi hodí jen source, runtime resolver nepotřebuju?
			//		- až na private callbacky - ten bude dostupný u public/protected property jen v kontextu parenta
			//		- u definice chceme vědět:
			//			- kde byla definovaná (source, např. traita)
			//			- v kontextu jaké třídy byla definovaná - důležité pro private properties a metody
			//			- v kontextu jaké (root) třídy běží resolve - pro public a protected
			//		- otestovat abstraktní metodu u parenta, přetíženou v potomkovi
			$lastPropertyStructure = $lastPropertyConfig['propertyStructure'];
			$lastContextReflector = $lastPropertyStructure->getContextReflector();

			//TODO - získat 0-1, resolvnout, odstranit ze seznamu na všech úrovních
			$default = $this->getDefaultValue(
				$lastPropertyStructure,
				$lastPropertyConfig['scopedDefinitions'][DefaultValueModifier::class] ?? [],
			);

			$context = new MetaFieldContext($this->loader, $this, $default);

			$resolvedDefinitions = [];
			foreach ($scopedProperty as $propertyClassName => $propertyConfig) {
				$propertyStructure = $propertyConfig['propertyStructure'];
				$this->checkProperty($rootClass, $propertyStructure);
				foreach ($propertyConfig['scopedDefinitions'] as $scope => $definitions) {
					foreach ($definitions as $definition) {
						if ($definition instanceof CallbackDefinition) {
							$resolvedDefinition = $this->resolveCallbackMeta(
								$definition,
								$context,
								$propertyStructure->getContextReflector(),
							);
							$resolvedDefinitions['callback'][$scope][] = $resolvedDefinition;
						} elseif ($definition instanceof DocDefinition) {
							//TODO - docs - currently unused
							$resolvedDefinition = $this->resolveDocMeta($definition, $context);
							$resolvedDefinitions['doc'][$scope][] = $resolvedDefinition;
						} elseif ($definition instanceof ModifierDefinition) {
							$resolvedDefinition = $this->resolveModifierMeta($definition, $context);
							$resolvedDefinitions['modifier'][$scope][] = $resolvedDefinition;
						} elseif ($definition instanceof RuleDefinition) {
							$resolvedDefinition = $this->resolveRuleMeta($definition, $context);
							$resolvedDefinitions['rule'][$scope][] = $resolvedDefinition;
						} else {
							$this->throwUnsupportedDefinitionType($definition);
						}
					}
				}
			}

			/*
			if ($repeatable === RepeatableBehavior::noRepeat()) {
				//TODO - kontrolovat, že je definice jenom jedna v téhle iteraci
				//TODO - kontrolovat, že ještě nastavená není z předchozí iterace
				$grouped[$propertyName][$scope][] = $definition;
			} elseif ($repeatable === RepeatableBehavior::merge()) {
				//TODO - mergnout
				$grouped[$propertyName][$scope][] = $definition;
			} elseif ($repeatable === RepeatableBehavior::override()) {
				//TODO - přepsat
				$grouped[$propertyName][$scope][] = $definition;
			}
			*/

			if (($resolvedDefinitions['rule'][Rule::class] ?? []) === []) {
				continue;
			}

			//TODO - getter validující existenci
			$field = new FieldRuntimeMeta(
				$resolvedDefinitions['callback'][BeforeValidationCallback::class] ?? [],
				$resolvedDefinitions['callback'][AfterValidationCallback::class] ?? [],
				$resolvedDefinitions['rule'][Rule::class][0],
				$default,
				PhpPropertyMeta::from($lastContextReflector),
			);

			//TODO - šlo by restrukturovat tak, aby phpstan chápal typy?
			//		- max přes getter, stejně jako byl ve FieldRuntimeMeta
			$fieldNameMeta = $resolvedDefinitions['modifier'][FieldNameModifier::class][0] ?? null;
			$fieldName = $fieldNameMeta !== null
				? $fieldNameMeta->args->name
				: $lastContextReflector->getName();

			$fields[$fieldName] = $field;
		}

		return $fields;
	}

	/**
	 * @param ReflectionClass<MappedObject> $rootClass
	 * @return never
	 */
	private function throwFieldHasNoRule(
		ReflectionClass $rootClass,
		CompileMeta $meta,
		FieldCompileMeta $firstFieldMeta
	): void
	{
		$propertyName = $this->getRelativePropertyName($firstFieldMeta->getPropertyStructure(), $rootClass);

		$message = Message::create()
			->withContext("Resolving metadata of '{$rootClass->getName()}'.")
			->withProblem(
				"Property '$propertyName' has some mapped object definition"
				. " (in {$meta->getSourceName()}), but no rule definition.",
			)
			->withSolution('Either remove the definition or add a rule definition.');

		throw InvalidArgument::create()
			->withMessage($message);
	}

	/**
	 * @param ReflectionClass<MappedObject> $rootClass
	 * @return never
	 */
	private function throwFieldHasMoreThanOneRule(
		ReflectionClass $rootClass,
		CompileMeta $meta,
		FieldCompileMeta $firstFieldMeta
	): void
	{
		$propertyName = $this->getRelativePropertyName($firstFieldMeta->getPropertyStructure(), $rootClass);

		$message = Message::create()
			->withContext("Resolving metadata of '{$rootClass->getName()}'.")
			->withProblem(
				"Property '$propertyName' has multiple rule definitions"
				. " (in {$meta->getSourceName()}), but only one is allowed.",
			)
			->withSolution("Combine multiple with '{$meta->getAnyOfSourceKey()}' or '{$meta->getAllOfSourceKey()}'.");

		throw InvalidArgument::create()
			->withMessage($message);
	}

	/**
	 * @param ReflectionClass<MappedObject> $rootClass
	 * @param list<FieldCompileMeta> $resolvedGroup
	 */
	private function checkFieldInvariance(ReflectionClass $rootClass, array $resolvedGroup, string $sourceName): void
	{
		//TODO
		//		- přejmenovat metodu
		//		- pravidla jsou invariantní
		//		- callbacky se mergují
		//		- docs se přetěžují
		//		- modifikátory jsou kus od kusu
		//      $previousFieldMeta = null;
		//      foreach ($resolvedGroup as $fieldMeta) {
		//          if (
		//              $previousFieldMeta !== null
		//              && $fieldMeta->getRules() !== []
		//              && $previousFieldMeta->getRules() !== []
		//              && $fieldMeta->getRules() != $previousFieldMeta->getRules()
		//          ) {
		//              $name = $this->getRelativePropertyName($fieldMeta->getProperty(), $rootClass);
		//              $previousName = $this->getRelativePropertyName($previousFieldMeta->getProperty(), $rootClass);
		//
		//              $message = Message::create()
		//                  ->withContext("Resolving metadata of '{$rootClass->getName()}'.")
		//                  ->withProblem(
		//                      "Definition in $sourceName of property '$name' differs from definition in $sourceName"
		//                      . " of property '$previousName'.",
		//                  )
		//                  ->withSolution("Don't override metadata of properties in child classes.");
		//
		//              throw InvalidArgument::create()
		//                  ->withMessage($message);
		//          }
		//
		//          $previousFieldMeta = $fieldMeta;
		//      }
	}

	/**
	 * @param ReflectionClass<MappedObject> $rootClass
	 */
	private function checkProperty(ReflectionClass $rootClass, PropertyStructure $propertyStructure): void
	{
		$reflector = $propertyStructure->getContextReflector();

		//TODO - asi target?? a kontrolovat ve scope resolver
		if ($reflector->isStatic()) {
			$message = Message::create()
				->withContext("Resolving metadata of '{$rootClass->getName()}'.")
				->withProblem(
					"Mapped property {$propertyStructure->getSource()->toString()} is static, but static properties are not supported.",
				)
				->withSolution('Make the property non-static.');

			throw InvalidArgument::create()
				->withMessage($message);
		}

		//TODO - scope resolver
		$classReflector = $reflector->getDeclaringClass();
		if (!$classReflector->isSubclassOf(MappedObject::class)) {
			$this->throwFieldMetaOutsideOfMappedObject($rootClass, $classReflector, $propertyStructure->getSource());
		}
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
			->withContext("Resolving metadata of '{$rootClass->getName()}'.")
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
	 * @param list<CallbackDefinition> $callbacks
	 * @param ReflectionClass<MappedObject>|ReflectionProperty $reflector
	 * @return array<class-string<Callback<Args>>, list<CallbackRuntimeMeta<Args>>>
	 */
	private function resolveCallbacksMeta(
		array $callbacks,
		MetaContext $context,
		Reflector $reflector
	): array
	{
		$array = [];
		foreach ($callbacks as $callback) {
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
		CallbackDefinition $definition,
		MetaContext $context,
		Reflector $reflector
	): CallbackRuntimeMeta
	{
		$handler = $definition->getHandler();
		$args = $handler::resolveArgs($definition->getArgs(), $context, $reflector);

		$argsType = $handler::getArgsType();
		if (!is_a($args, $argsType)) {
			$realArgsType = get_class($args);

			throw InvalidArgument::create()
				->withMessage(
					"'{$handler}::resolveArgs()' should return '$argsType' (as defined in 'getArgsType()' method)"
					. ", but returns '$realArgsType'.",
				);
		}

		return new CallbackRuntimeMeta($handler, $args);
	}

	/**
	 * @param list<DocDefinition> $docs
	 * @return array<string, DocMeta>
	 */
	private function resolveDocsMeta(array $docs, MetaContext $context): array
	{
		$array = [];
		foreach ($docs as $doc) {
			$array[$doc->getHandler()::getUniqueName()] = $this->resolveDocMeta($doc, $context);
		}

		return $array;
	}

	public function resolveDocMeta(DocDefinition $definition, MetaContext $context): DocMeta
	{
		$handler = $definition->getHandler();
		$args = $handler::resolveArgs($definition->getArgs(), $context);

		return new DocMeta($handler, $args);
	}

	/**
	 * @param list<ModifierDefinition> $modifiers
	 * @return array<class-string<Modifier<Args>>, list<ModifierRuntimeMeta<Args>>>
	 */
	private function resolveClassModifiersMeta(array $modifiers, MetaContext $context): array
	{
		$array = [];
		foreach ($modifiers as $modifier) {
			$array[$modifier->getHandler()][] = $this->resolveModifierMeta($modifier, $context);
		}

		return $array;
	}

	/**
	 * @param list<ModifierDefinition> $modifiers
	 * @return array<class-string<Modifier<Args>>, ModifierRuntimeMeta<Args>>
	 */
	private function resolveFieldModifiersMeta(array $modifiers, MetaContext $context): array
	{
		$array = [];
		foreach ($modifiers as $modifier) {
			$array[$modifier->getHandler()] = $this->resolveModifierMeta($modifier, $context);
		}

		return $array;
	}

	/**
	 * @return ModifierRuntimeMeta<Args>
	 */
	private function resolveModifierMeta(ModifierDefinition $definition, MetaContext $context): ModifierRuntimeMeta
	{
		$handler = $definition->getHandler();
		$args = $handler::resolveArgs($definition->getArgs(), $context);

		return new ModifierRuntimeMeta($handler, $args);
	}

	/**
	 * @return RuleRuntimeMeta<Args>
	 */
	public function resolveRuleMeta(RuleDefinition $definition, MetaFieldContext $context): RuleRuntimeMeta
	{
		$handler = $definition->getHandler();
		$rule = $this->ruleManager->getRule($handler);
		$args = $rule->resolveArgs($definition->getArgs(), $context);

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

		return new RuleRuntimeMeta($handler, $args);
	}

	/**
	 * @param list<MetaDefinition> $defaultModifiers
	 */
	private function getDefaultValue(PropertyStructure $propertyStructure, array $defaultModifiers): DefaultValueMeta
	{
		foreach ($defaultModifiers as $modifier) {
			//TODO - používat až po resolve
			return DefaultValueMeta::fromValue($modifier->getArgs()[DefaultValueModifier::Value]);
		}

		$property = $propertyStructure->getContextReflector();
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
		foreach ($meta->getGroupedProperties() as $fieldMetas) {
			foreach ($fieldMetas as $fieldMeta) {
				if ($fieldMeta->getDefinitions() === []) {
					continue;
				}

				$propertyStructure = $fieldMeta->getPropertyStructure();
				$property = $propertyStructure->getContextReflector();

				$fieldName = $property->getName();

				foreach ($fieldMeta->getDefinitions() as $definition) {
					if ($definition->getHandler() === FieldNameModifier::class) {
						$fieldName = $definition->getArgs()[FieldNameModifier::Name];
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
							->withContext("Resolving metadata of '{$rootClass->getName()}'.")
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
