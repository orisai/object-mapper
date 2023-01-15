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
		$this->checkFieldNames($meta);

		return new RuntimeMeta(
			$this->resolveClassMeta($meta, $class),
			$this->resolveFieldsMeta($meta),
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
			$this->resolveCallbacksMeta($classMeta, $context, $class),
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
		$this->objectCreator->checkClassIsInstantiable(
			$class->getName(),
			$meta->getModifier(CreateWithoutConstructorModifier::class) === null,
		);
	}

	/**
	 * @return array<int|string, FieldRuntimeMeta>
	 */
	private function resolveFieldsMeta(CompileMeta $meta): array
	{
		$fields = [];
		foreach ($meta->getFields() as $fieldMeta) {
			$property = $fieldMeta->getProperty();
			$fieldName = $this->propertyNameToFieldName($fieldMeta);
			$fields[$fieldName] = $this->resolveFieldMeta(
				$fieldMeta,
				$property,
				$this->getDefaultValue($fieldMeta, $property),
			);
		}

		return $fields;
	}

	/**
	 * @return int|string
	 */
	private function propertyNameToFieldName(FieldCompileMeta $fieldMeta)
	{
		foreach ($fieldMeta->getModifiers() as $modifier) {
			if ($modifier->getType() === FieldNameModifier::class) {
				return $modifier->getArgs()[FieldNameModifier::Name];
			}
		}

		return $fieldMeta->getProperty()->getName();
	}

	private function resolveFieldMeta(
		FieldCompileMeta $meta,
		ReflectionProperty $property,
		DefaultValueMeta $defaultValue
	): FieldRuntimeMeta
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

		$context = ResolverArgsContext::forProperty($property, $this);

		return new FieldRuntimeMeta(
			$this->resolveCallbacksMeta($meta, $context, $property->getDeclaringClass()),
			$this->resolveDocsMeta($meta, $context),
			$this->resolveModifiersMeta($meta, $context),
			$this->resolveRuleMeta(
				$meta->getRule(),
				$this->createRuleArgsContext($property),
			),
			$defaultValue,
			$property,
		);
	}

	/**
	 * @param ReflectionClass<MappedObject> $declaringClass
	 * @return array<int, CallbackRuntimeMeta<Args>>
	 */
	private function resolveCallbacksMeta(
		NodeCompileMeta $meta,
		ResolverArgsContext $context,
		ReflectionClass $declaringClass
	): array
	{
		$array = [];
		foreach ($meta->getCallbacks() as $key => $callback) {
			$array[$key] = $this->resolveCallbackMeta($callback, $context, $declaringClass);
		}

		return $array;
	}

	/**
	 * @param ReflectionClass<MappedObject> $declaringClass
	 * @return CallbackRuntimeMeta<Args>
	 */
	private function resolveCallbackMeta(
		CallbackCompileMeta $meta,
		ResolverArgsContext $context,
		ReflectionClass $declaringClass
	): CallbackRuntimeMeta
	{
		$type = $meta->getType();

		return new CallbackRuntimeMeta(
			$type,
			$type::resolveArgs($meta->getArgs(), $context),
			$declaringClass,
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

	private function getDefaultValue(FieldCompileMeta $meta, ReflectionProperty $property): DefaultValueMeta
	{
		$propertyName = $property->getName();

		$defaults = $property->getDeclaringClass()->getDefaultProperties();
		if (!array_key_exists($propertyName, $defaults)) {
			return DefaultValueMeta::fromNothing();
		}

		$propertyValue = $defaults[$propertyName];

		$isPropertyTyped = $property->hasType();
		$containsNullable = $meta->getRule()->containsAnyOfRules(
			[NullRule::class, MixedRule::class],
		);

		// It's not possible to distinguish between null and uninitialized for properties without type,
		// default null is used only if validation allows null
		return $propertyValue === null && !$isPropertyTyped && !$containsNullable
			? DefaultValueMeta::fromNothing()
			: DefaultValueMeta::fromValue($propertyValue);
	}

	private function checkFieldNames(CompileMeta $meta): void
	{
		$map = [];
		foreach ($meta->getFields() as $field) {
			$property = $field->getProperty();

			$fieldName = $property->getName();
			$source = 'property name';

			foreach ($field->getModifiers() as $modifier) {
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

	private function createRuleArgsContext(ReflectionProperty $property): RuleArgsContext
	{
		return new RuleArgsContext($property, $this->ruleManager, $this->loader, $this);
	}

}
