<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Meta;

use Orisai\Exceptions\Logic\InvalidArgument;
use Orisai\Exceptions\Logic\InvalidState;
use Orisai\Exceptions\Message;
use Orisai\ObjectMapper\Args\Args;
use Orisai\ObjectMapper\Context\ArgsContext;
use Orisai\ObjectMapper\MappedObject;
use Orisai\ObjectMapper\Meta\Compile\CallbackCompileMeta;
use Orisai\ObjectMapper\Meta\Compile\ClassCompileMeta;
use Orisai\ObjectMapper\Meta\Compile\CompileMeta;
use Orisai\ObjectMapper\Meta\Compile\FieldCompileMeta;
use Orisai\ObjectMapper\Meta\Compile\ModifierCompileMeta;
use Orisai\ObjectMapper\Meta\Compile\NodeCompileMeta;
use Orisai\ObjectMapper\Meta\Compile\RuleCompileMeta;
use Orisai\ObjectMapper\Meta\Runtime\CallbackRuntimeMeta;
use Orisai\ObjectMapper\Meta\Runtime\ClassRuntimeMeta;
use Orisai\ObjectMapper\Meta\Runtime\FieldRuntimeMeta;
use Orisai\ObjectMapper\Meta\Runtime\ModifierRuntimeMeta;
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
use ReflectionClass;
use ReflectionProperty;
use Reflector;
use function array_key_exists;
use function array_merge;
use function get_class;
use function sprintf;
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
		$this->checkFieldNames($meta);

		$runtimeMeta = new RuntimeMeta(
			$this->resolveClassMeta($meta),
			$this->resolveFieldsMeta($meta),
		);

		$this->checkObjectCanBeInstantiated($class, $runtimeMeta->getClass());

		return $runtimeMeta;
	}

	private function resolveClassMeta(CompileMeta $meta): ClassRuntimeMeta
	{
		$callbacksByMeta = [];
		$docsByMeta = [];
		$modifiersByMeta = [];

		foreach ($meta->getClasses() as $classMeta) {
			$class = $classMeta->getClass();

			$context = new ArgsContext($this->loader, $this);

			$callbacksByMeta[] = $this->resolveCallbacksMeta($classMeta, $context, $class->getContextReflector());
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
	 * @param ReflectionClass<MappedObject> $class
	 */
	private function checkObjectCanBeInstantiated(ReflectionClass $class, ClassRuntimeMeta $meta): void
	{
		$injectors = [];
		foreach ($meta->getModifier(RequiresDependenciesModifier::class) as $modifier) {
			$injectors[] = $modifier->getArgs()->injector;
		}

		$this->objectCreator->createInstance($class->getName(), $injectors);
	}

	/**
	 * @return array<int|string, FieldRuntimeMeta>
	 */
	private function resolveFieldsMeta(CompileMeta $meta): array
	{
		$fields = [];
		foreach ($meta->getFields() as $fieldMeta) {
			$resolved = $this->resolveFieldMeta(
				$fieldMeta,
				$this->getDefaultValue($fieldMeta),
			);

			$fieldName = $this->propertyNameToFieldName($resolved);
			$fields[$fieldName] = $resolved;
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
			return $modifier->getArgs()->name;
		}

		return $fieldMeta->getProperty()->getName();
	}

	private function resolveFieldMeta(
		FieldCompileMeta $meta,
		DefaultValueMeta $defaultValue
	): FieldRuntimeMeta
	{
		$property = $meta->getProperty()->getContextReflector();

		if ($property->isStatic()) {
			throw InvalidArgument::create()
				->withMessage(sprintf(
					'Property %s::$%s is not valid mapped property, \'%s\' supports only non-static properties to be mapped.',
					$property->getDeclaringClass()->getName(),
					$property->getName(),
					MappedObject::class,
				));
		}

		$context = new ArgsContext($this->loader, $this);

		return new FieldRuntimeMeta(
			$this->resolveCallbacksMeta($meta, $context, $property),
			$this->resolveDocsMeta($meta, $context),
			$this->resolveFieldModifiersMeta($meta, $context),
			$this->resolveRuleMeta(
				$meta->getRule(),
				$this->createArgsContext(),
			),
			$defaultValue,
			$property,
		);
	}

	/**
	 * @param ReflectionClass<MappedObject>|ReflectionProperty $reflector
	 * @return array<int, CallbackRuntimeMeta<Args>>
	 */
	private function resolveCallbacksMeta(
		NodeCompileMeta $meta,
		ArgsContext $context,
		Reflector $reflector
	): array
	{
		$array = [];
		foreach ($meta->getCallbacks() as $key => $callback) {
			$array[$key] = $this->resolveCallbackMeta(
				$callback,
				$context,
				$reflector,
				$meta->getClass()->getContextReflector(),
			);
		}

		return $array;
	}

	/**
	 * @param ReflectionClass<MappedObject>|ReflectionProperty $reflector
	 * @param ReflectionClass<MappedObject>                   $declaringClass
	 * @return CallbackRuntimeMeta<Args>
	 */
	private function resolveCallbackMeta(
		CallbackCompileMeta $meta,
		ArgsContext $context,
		Reflector $reflector,
		ReflectionClass $declaringClass
	): CallbackRuntimeMeta
	{
		$type = $meta->getType();

		return new CallbackRuntimeMeta(
			$type,
			$type::resolveArgs($meta->getArgs(), $context, $reflector),
			$declaringClass,
		);
	}

	/**
	 * @return array<string, DocMeta>
	 */
	private function resolveDocsMeta(NodeCompileMeta $meta, ArgsContext $context): array
	{
		$array = [];
		foreach ($meta->getDocs() as $doc) {
			$array[$doc->getName()::getUniqueName()] = $this->resolveDocMeta($doc, $context);
		}

		return $array;
	}

	public function resolveDocMeta(DocMeta $meta, ArgsContext $context): DocMeta
	{
		$type = $meta->getName();
		$args = $type::resolveArgs($meta->getArgs(), $context);

		return new DocMeta($type, $args);
	}

	/**
	 * @return array<class-string<Modifier<Args>>, list<ModifierRuntimeMeta<Args>>>
	 */
	private function resolveClassModifiersMeta(ClassCompileMeta $meta, ArgsContext $context): array
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
	private function resolveFieldModifiersMeta(FieldCompileMeta $meta, ArgsContext $context): array
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
	private function resolveModifierMeta(ModifierCompileMeta $meta, ArgsContext $context): ModifierRuntimeMeta
	{
		$type = $meta->getType();
		$args = $type::resolveArgs($meta->getArgs(), $context);

		return new ModifierRuntimeMeta($type, $args);
	}

	/**
	 * @return RuleRuntimeMeta<Args>
	 */
	public function resolveRuleMeta(RuleCompileMeta $meta, ArgsContext $context): RuleRuntimeMeta
	{
		$type = $meta->getType();
		$rule = $this->ruleManager->getRule($type);
		$args = $rule->resolveArgs($meta->getArgs(), $context);

		$argsType = $rule->getArgsType();
		$argsRef = new ReflectionClass($argsType);

		if ($argsRef->isAbstract() || $argsRef->isInterface() || $argsRef->isTrait()) {
			$ruleClass = get_class($rule);

			throw InvalidArgument::create()
				->withMessage("Class $argsType returned by $ruleClass::getArgsType() must be instantiable.");
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

	private function checkFieldNames(CompileMeta $meta): void
	{
		$map = [];
		foreach ($meta->getFields() as $fieldMeta) {
			$property = $fieldMeta->getProperty()->getContextReflector();

			$fieldName = $property->getName();
			$source = 'property name';

			foreach ($fieldMeta->getModifiers() as $modifier) {
				if ($modifier->getType() === FieldNameModifier::class) {
					$fieldName = $modifier->getArgs()[FieldNameModifier::Name];
					$source = 'field name meta';

					break;
				}
			}

			$colliding = $map[$fieldName] ?? null;
			if ($colliding !== null) {
				$fullName = "{$property->getDeclaringClass()->getName()}::\${$property->getName()}";

				$collidingProperty = $colliding['property'];
				$collidingFullName = "{$collidingProperty->getDeclaringClass()->getName()}::\${$collidingProperty->getName()}";
				$collidingSource = $colliding['source'];

				$message = Message::create()
					->withContext("Validating mapped property '$fullName'.")
					->withProblem("Field name '$fieldName' defined in $source collides with " .
						"field name of property '$collidingFullName' defined in $collidingSource.")
					->withSolution('Define unique field name for each mapped property.');

				throw InvalidState::create()
					->withMessage($message);
			}

			$map[$fieldName] = [
				'property' => $property,
				'source' => $source,
			];
		}
	}

	private function createArgsContext(): ArgsContext
	{
		return new ArgsContext($this->loader, $this);
	}

}
