<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Processing;

use Nette\Utils\Helpers;
use Orisai\Exceptions\Logic\InvalidState;
use Orisai\ObjectMapper\Callbacks\AfterCallback;
use Orisai\ObjectMapper\Callbacks\BeforeCallback;
use Orisai\ObjectMapper\Callbacks\Callback;
use Orisai\ObjectMapper\Context\BaseFieldContext;
use Orisai\ObjectMapper\Context\FieldContext;
use Orisai\ObjectMapper\Context\FieldSetContext;
use Orisai\ObjectMapper\Context\ProcessorRunContext;
use Orisai\ObjectMapper\Context\SkippedPropertiesContext;
use Orisai\ObjectMapper\Context\SkippedPropertyContext;
use Orisai\ObjectMapper\Context\TypeContext;
use Orisai\ObjectMapper\Creation\ObjectCreator;
use Orisai\ObjectMapper\Exception\InvalidData;
use Orisai\ObjectMapper\Exception\ValueDoesNotMatch;
use Orisai\ObjectMapper\Meta\ArgsCreator;
use Orisai\ObjectMapper\Meta\ClassMeta;
use Orisai\ObjectMapper\Meta\Meta;
use Orisai\ObjectMapper\Meta\MetaLoader;
use Orisai\ObjectMapper\Meta\PropertyMeta;
use Orisai\ObjectMapper\Meta\SharedMeta;
use Orisai\ObjectMapper\Modifiers\FieldNameModifier;
use Orisai\ObjectMapper\Modifiers\SkippedModifier;
use Orisai\ObjectMapper\NoValue;
use Orisai\ObjectMapper\Options;
use Orisai\ObjectMapper\Rules\RuleManager;
use Orisai\ObjectMapper\Rules\StructureArgs;
use Orisai\ObjectMapper\Rules\StructureRule;
use Orisai\ObjectMapper\Types\MessageType;
use Orisai\ObjectMapper\Types\StructureType;
use Orisai\ObjectMapper\ValueObject;
use function array_diff;
use function array_key_exists;
use function array_keys;
use function array_map;
use function assert;
use function get_class;
use function implode;
use function in_array;
use function is_a;
use function is_array;
use function sprintf;

class DefaultProcessor implements Processor
{

	use ArgsCreator;

	protected MetaLoader $metaLoader;

	protected RuleManager $ruleManager;

	protected ObjectCreator $creator;

	protected ?TypeContext $typeContext = null;

	public function __construct(MetaLoader $metaLoader, RuleManager $ruleManager, ObjectCreator $creator)
	{
		$this->metaLoader = $metaLoader;
		$this->ruleManager = $ruleManager;
		$this->creator = $creator;
	}

	/**
	 * @param mixed $data
	 * @throws InvalidData
	 */
	public function process($data, string $class, ?Options $options = null): ValueObject
	{
		$options ??= new Options();
		$type = $this->createStructureType($class);
		$holder = $this->createHolder($class);

		$fieldSetContext = $this->createFieldSetContext($options, $type, true);
		$runContext = $this->createProcessorRunContext($class, $holder);

		$processedData = $this->processData($data, $fieldSetContext, $runContext);

		$object = $holder->getInstance();
		$this->fillObject($object, $processedData, $data, $fieldSetContext, $runContext);

		return $object;
	}

	/**
	 * @param mixed $data
	 * @param class-string<ValueObject> $class
	 * @return array<int|string, mixed>
	 * @throws InvalidData
	 */
	public function processWithoutInitialization($data, string $class, ?Options $options = null): array
	{
		$options ??= new Options();
		$type = $this->createStructureType($class);
		$holder = $this->createHolder($class);

		$fieldSetContext = $this->createFieldSetContext($options, $type, false);
		$runContext = $this->createProcessorRunContext($class, $holder);

		return $this->processData($data, $fieldSetContext, $runContext);
	}

	protected function getTypeContext(): TypeContext
	{
		if ($this->typeContext === null) {
			$this->typeContext = new TypeContext($this->metaLoader, $this->ruleManager);
		}

		return $this->typeContext;
	}

	// /////////////// //
	// Base processing //
	// /////////////// //

	/**
	 * @param mixed $data
	 * @return array<int|string, mixed>
	 * @throws InvalidData
	 */
	protected function processData(
		$data,
		FieldSetContext $fieldSetContext,
		ProcessorRunContext $runContext
	): array
	{
		$meta = $runContext->getMeta();
		$classMeta = $meta->getClass();

		$data = $this->ensureDataProcessable($data, $fieldSetContext);
		$data = $this->handleClassCallbacks($data, $fieldSetContext, $runContext, $classMeta, BeforeCallback::class);
		$data = $this->handleFields($data, $fieldSetContext, $runContext);
		$data = $this->handleClassCallbacks($data, $fieldSetContext, $runContext, $classMeta, AfterCallback::class);

		return $data;
	}

	/**
	 * @param class-string<ValueObject> $class
	 */
	protected function createStructureType(string $class): StructureType
	{
		$type = $this->ruleManager->getRule(StructureRule::class)->createType(
			StructureArgs::fromClass($class),
			$this->getTypeContext(),
		);
		assert($type instanceof StructureType);

		return $type;
	}

	protected function createFieldSetContext(
		Options $options,
		StructureType $type,
		bool $initializeObjects
	): FieldSetContext
	{
		return new FieldSetContext($this->metaLoader, $this->ruleManager, $this, $options, $type, $initializeObjects);
	}

	/**
	 * @param class-string<ValueObject> $class
	 */
	protected function createProcessorRunContext(string $class, ObjectHolder $holder): ProcessorRunContext
	{
		$meta = $this->metaLoader->load($class);

		return new ProcessorRunContext($class, $holder, $meta);
	}

	/**
	 * @param mixed $data
	 * @return array<mixed>
	 * @throws InvalidData
	 */
	protected function ensureDataProcessable($data, FieldSetContext $context): array
	{
		if (!is_array($data)) {
			$type = $context->getType();
			$type->markInvalid();

			throw InvalidData::create($type, $data);
		}

		return $data;
	}

	/**
	 * @param int|string $fieldName
	 */
	protected function fieldNameToPropertyName($fieldName, Meta $meta): string
	{
		$map = $meta->getFieldsPropertiesMap();

		return $map[$fieldName] ?? (string) $fieldName;
	}

	/**
	 * @return int|string
	 */
	protected function propertyNameToFieldName(string $propertyName, PropertyMeta $meta)
	{
		$fieldNameMeta = $meta->getModifier(FieldNameModifier::class);
		if ($fieldNameMeta !== null) {
			return $fieldNameMeta->getArgs()[FieldNameModifier::NAME];
		}

		return $propertyName;
	}

	// /////////////////// //
	// Properties / Fields //
	// /////////////////// //

	/**
	 * @param array<int|string, mixed> $data
	 * @return array<int|string, mixed>
	 * @throws InvalidData
	 */
	protected function handleFields(
		array $data,
		FieldSetContext $fieldSetContext,
		ProcessorRunContext $runContext
	): array
	{
		$data = $this->handleSentFields($data, $fieldSetContext, $runContext);
		$data = $this->handleMissingFields($data, $fieldSetContext, $runContext);

		$type = $fieldSetContext->getType();

		if ($type->hasInvalidFields()) {
			throw InvalidData::create($type, NoValue::create());
		}

		return $data;
	}

	/**
	 * @param array<int|string, mixed> $data
	 * @return array<int|string, mixed>
	 */
	protected function handleSentFields(
		array $data,
		FieldSetContext $fieldSetContext,
		ProcessorRunContext $runContext
	): array
	{
		$type = $fieldSetContext->getType();

		$meta = $runContext->getMeta();
		$propertiesMeta = $meta->getProperties();
		/** @var array<string> $propertyNames */
		$propertyNames = array_keys($propertiesMeta);

		foreach ($data as $fieldName => $value) {
			// Skip invalid field
			if ($type->isFieldInvalid($fieldName)) {
				continue;
			}

			$propertyName = $this->fieldNameToPropertyName($fieldName, $meta);

			// Unknown field
			if (!isset($propertiesMeta[$propertyName])) {
				// Remove field from data
				unset($data[$fieldName]);

				// Add error to type
				$hintedPropertyName = Helpers::getSuggestion($propertyNames, $propertyName);
				$hintedFieldName = $hintedPropertyName !== null ?
					$this->propertyNameToFieldName(
						$hintedPropertyName,
						$propertiesMeta[$hintedPropertyName],
					)
					: null;
				$hint = ($hintedFieldName !== null ? sprintf(', did you mean `%s`?', $hintedFieldName) : '.');
				$type->overwriteInvalidField(
					$fieldName,
					ValueDoesNotMatch::create(
						new MessageType(sprintf('Field is unknown%s', $hint)),
						$value,
					),
				);

				continue;
			}

			$propertyMeta = $propertiesMeta[$propertyName];
			$fieldContext = $this->createFieldContext($fieldSetContext, $propertyMeta, $fieldName, $propertyName);

			// Skip skipped property
			if (
				$fieldSetContext->isInitializeObjects()
				&& $propertyMeta->getModifier(SkippedModifier::class) !== null
			) {
				$runContext->addSkippedProperty(
					$propertyName,
					new SkippedPropertyContext($fieldName, $value, false),
				);
				unset($data[$fieldName]);

				continue;
			}

			// Process field value with property rules
			try {
				$data[$fieldName] = $this->processProperty(
					$value,
					$fieldContext,
					$runContext,
					$propertyMeta,
				);
			} catch (ValueDoesNotMatch | InvalidData $exception) {
				$type->overwriteInvalidField($fieldName, $exception);
			}
		}

		return $data;
	}

	/**
	 * @param array<int|string, mixed> $data
	 * @return array<string>
	 */
	protected function findMissingProperties(array $data, ProcessorRunContext $runContext): array
	{
		$meta = $runContext->getMeta();

		return array_diff(
			array_keys($meta->getProperties()),
			array_map(
				fn ($fieldName): string => $this->fieldNameToPropertyName($fieldName, $meta),
				array_keys($data),
			),
		);
	}

	/**
	 * @return array<string>
	 */
	protected function getSkippedProperties(ProcessorRunContext $runContext): array
	{
		return array_keys($runContext->getSkippedProperties());
	}

	/**
	 * @param array<int|string, mixed> $data
	 * @return array<int|string, mixed>
	 */
	protected function handleMissingFields(
		array $data,
		FieldSetContext $fieldSetContext,
		ProcessorRunContext $runContext
	): array
	{
		$type = $fieldSetContext->getType();
		$options = $fieldSetContext->getOptions();
		$initializeObjects = $fieldSetContext->isInitializeObjects();

		$propertiesMeta = $runContext->getMeta()->getProperties();

		$missingProperties = $this->findMissingProperties($data, $runContext);
		$requiredFields = $options->getRequiredFields();
		$fillDefaultValues = $initializeObjects || $options->isPreFillDefaultValues();

		$skippedProperties = $this->getSkippedProperties($runContext);

		foreach ($missingProperties as $missingProperty) {
			// Skipped properties are not considered missing, they are just processed later
			if (in_array($missingProperty, $skippedProperties, true)) {
				continue;
			}

			$propertyMeta = $propertiesMeta[$missingProperty];
			$defaultMeta = $propertyMeta->getDefault();
			$missingField = $this->propertyNameToFieldName($missingProperty, $propertyMeta);

			if ($requiredFields === $options::REQUIRE_NON_DEFAULT && $defaultMeta->hasValue()) {
				// Add default value if defaults are not required and should be used
				// If VOs are initialized then values are always prefilled - user can work with them in after callback
				//   and they are defined by VO anyway
				if ($fillDefaultValues) {
					$data[$missingField] = $defaultMeta->getValue();
				}
			} elseif (
				$requiredFields === $options::REQUIRE_NON_DEFAULT
				&& is_a($propertyMeta->getRule()->getType(), StructureRule::class, true)
			) {
				// Try initialize structure from empty array when no data given
				// Structure in compound type is not supported (allOf, anyOf)
				// Used only in default mode - if all or none values are required then we need differentiate whether user sent value or not
				$structureArgs = StructureArgs::fromArray($propertyMeta->getRule()->getArgs());
				try {
					$data[$missingField] = $initializeObjects
						? $this->process([], $structureArgs->type, $options)
						: $this->processWithoutInitialization([], $structureArgs->type, $options);
				} catch (InvalidData $exception) {
					$type->overwriteInvalidField(
						$missingField,
						InvalidData::create($exception->getInvalidType(), NoValue::create()),
					);
				}
			} elseif ($requiredFields !== $options::REQUIRE_NONE && !$type->isFieldInvalid($missingField)) {
				// Field is missing and have no default value, mark as invalid
				$propertyRuleMeta = $propertyMeta->getRule();
				$propertyRule = $this->ruleManager->getRule($propertyRuleMeta->getType());
				$type->overwriteInvalidField(
					$missingField,
					ValueDoesNotMatch::create(
						$propertyRule->createType(
							$this->createRuleArgsInst($propertyRule, $propertyRuleMeta),
							$this->getTypeContext(),
						),
						NoValue::create(),
					),
				);
			}

			// Return skipped property separately
			if (
				array_key_exists($missingField, $data)
				&& $fieldSetContext->isInitializeObjects()
				&& $propertyMeta->getModifier(SkippedModifier::class) !== null
			) {
				$runContext->addSkippedProperty(
					$missingProperty,
					new SkippedPropertyContext($missingField, $data[$missingField], true),
				);
				unset($data[$missingField]);

				continue;
			}
		}

		return $data;
	}

	// //////////////// //
	// Property / Field //
	// //////////////// //

	/**
	 * @param int|string $fieldName
	 */
	protected function createFieldContext(
		FieldSetContext $fieldSetContext,
		PropertyMeta $meta,
		$fieldName,
		string $propertyName
	): FieldContext
	{
		$parentType = $fieldSetContext->getType();

		return new FieldContext(
			$this->metaLoader,
			$this->ruleManager,
			$this,
			$fieldSetContext->getOptions(),
			$parentType->getFields()[$fieldName],
			$meta->getDefault(),
			$fieldSetContext->isInitializeObjects(),
			$fieldName,
			$propertyName,
		);
	}

	/**
	 * @param mixed $value
	 * @return mixed
	 * @throws ValueDoesNotMatch
	 * @throws InvalidData
	 */
	protected function processProperty(
		$value,
		FieldContext $fieldContext,
		ProcessorRunContext $runContext,
		PropertyMeta $meta
	)
	{
		$value = $this->applyCallbacks($value, $fieldContext, $runContext, $meta, BeforeCallback::class);
		$value = $this->processPropertyRules($value, $fieldContext, $meta);
		$value = $this->applyCallbacks($value, $fieldContext, $runContext, $meta, AfterCallback::class);

		return $value;
	}

	/**
	 * @param mixed $value
	 * @return mixed
	 * @throws ValueDoesNotMatch
	 * @throws InvalidData
	 */
	protected function processPropertyRules($value, FieldContext $fieldContext, PropertyMeta $meta)
	{
		$ruleMeta = $meta->getRule();
		$rule = $this->ruleManager->getRule($ruleMeta->getType());

		return $rule->processValue(
			$value,
			$this->createRuleArgsInst($rule, $ruleMeta),
			$fieldContext,
		);
	}

	// ///////// //
	// Callbacks //
	// ///////// //

	/**
	 * @param array<mixed> $data
	 * @param class-string<Callback> $callbackType
	 * @return array<mixed>
	 * @throws InvalidData
	 */
	protected function handleClassCallbacks(
		array $data,
		FieldSetContext $fieldSetContext,
		ProcessorRunContext $runContext,
		ClassMeta $meta,
		string $callbackType
	): array
	{
		$type = $fieldSetContext->getType();

		try {
			$data = $this->applyCallbacks($data, $fieldSetContext, $runContext, $meta, $callbackType);
			assert(is_array($data)); // Class callbacks are forced to define return type
		} catch (ValueDoesNotMatch | InvalidData $exception) {
			$caughtType = $exception->getInvalidType();

			// User thrown type is not the actual type from FieldSetContext
			if ($caughtType !== $type) {
				$type->addError($exception);

				throw InvalidData::create($type, NoValue::create());
			}

			throw InvalidData::create($type, $data);
		}

		return $data;
	}

	/**
	 * @param mixed $data
	 * @param FieldContext|FieldSetContext $baseFieldContext
	 * @param ClassMeta|PropertyMeta $meta
	 * @param class-string<Callback> $callbackType
	 * @return mixed
	 * @throws ValueDoesNotMatch
	 * @throws InvalidData
	 */
	protected function applyCallbacks(
		$data,
		BaseFieldContext $baseFieldContext,
		ProcessorRunContext $runContext,
		SharedMeta $meta,
		string $callbackType
	)
	{
		$holder = $runContext->getObjectHolder();

		foreach ($meta->getCallbacks() as $callback) {
			if ($callback->getType() === $callbackType) {
				$data = $callbackType::invoke(
					$data,
					$this->createCallbackArgsInst($callbackType, $callback),
					$holder,
					$baseFieldContext,
				);
			}
		}

		return $data;
	}


	// //////////// //
	// Value Object //
	// //////////// //

	/**
	 * @param array<int|string, mixed> $data
	 * @param array<mixed> $rawData
	 */
	protected function fillObject(
		ValueObject $object,
		array $data,
		array $rawData,
		FieldSetContext $fieldSetContext,
		ProcessorRunContext $runContext
	): void
	{
		$type = $fieldSetContext->getType();
		$options = $fieldSetContext->getOptions();
		$meta = $runContext->getMeta();

		// Set raw data
		if ($options->isFillRawValues()) {
			$object->setRawValues($rawData);
		}

		// Reset mapped properties state
		$propertiesMeta = $runContext->getMeta()->getProperties();
		foreach ($propertiesMeta as $propertyName => $propertyMeta) {
			unset($object->$propertyName);
		}

		// Set processed data
		foreach ($data as $fieldName => $value) {
			$propertyName = $this->fieldNameToPropertyName($fieldName, $meta);
			$object->$propertyName = $value;
		}

		// Set skipped properties
		$skippedProperties = $runContext->getSkippedProperties();
		if ($skippedProperties !== []) {
			$partial = new SkippedPropertiesContext($type, $options);
			$object->setSkippedPropertiesContext($partial);

			foreach ($skippedProperties as $propertyName => $skippedPropertyContext) {
				$partial->addSkippedProperty($propertyName, $skippedPropertyContext);
			}
		}
	}

	/**
	 * @param class-string<ValueObject> $class
	 */
	protected function createHolder(string $class, ?ValueObject $object = null): ObjectHolder
	{
		return new ObjectHolder($this->creator, $class, $object);
	}

	// ////////////// //
	// Late processing //
	// ////////////// //

	/**
	 * @param array<string> $properties
	 * @throws InvalidData
	 */
	public function processSkippedProperties(
		array $properties,
		ValueObject $object,
		?Options $options = null
	): void
	{
		$class = get_class($object);

		// Object has no skipped properties
		if (!$object->hasSkippedPropertiesContext()) {
			throw InvalidState::create()
				->withMessage(sprintf(
					'Cannot initialize properties "%s" of "%s" instance because it has no skipped properties.',
					implode(', ', $properties),
					$class,
				));
		}

		$skippedPropertiesContext = $object->getSkippedPropertiesContext();

		$type = $skippedPropertiesContext->getType();
		$options ??= $skippedPropertiesContext->getOptions();
		$fieldSetContext = $this->createFieldSetContext($options, $type, true);
		$skippedProperties = $skippedPropertiesContext->getSkippedProperties();

		$holder = $this->createHolder($class, $object);
		$runContext = $this->createProcessorRunContext($class, $holder);
		$propertiesMeta = $this->metaLoader->load($class)->getProperties();

		foreach ($properties as $propertyName) {
			// Property is initialized or does not exist
			if (!array_key_exists($propertyName, $skippedProperties)) {
				throw InvalidState::create()
					->withMessage(sprintf(
						'Cannot initialize property "%s" of "%s" instance because it is already initialized or does not exist.',
						$propertyName,
						$class,
					));
			}

			$skippedPropertyContext = $skippedProperties[$propertyName];
			$propertyMeta = $propertiesMeta[$propertyName];

			$fieldName = $skippedPropertyContext->getFieldName();
			$fieldContext = $this->createFieldContext($fieldSetContext, $propertyMeta, $fieldName, $propertyName);

			// Process field value with property rules
			try {
				$processed = $skippedPropertyContext->isDefault()
					? $skippedPropertyContext->getValue()
					: $this->processProperty(
						$skippedPropertyContext->getValue(),
						$fieldContext,
						$runContext,
						$propertyMeta,
					);
				$object->$propertyName = $processed;
				$skippedPropertiesContext->removeInitializedProperty($propertyName);
			} catch (ValueDoesNotMatch | InvalidData $exception) {
				$type->overwriteInvalidField($fieldName, $exception);
			}
		}

		// If any of fields is invalid, throw error
		if ($type->hasInvalidFields()) {
			throw InvalidData::create($type, NoValue::create());
		}

		// Object is fully initialized, remove partial context
		if ($skippedPropertiesContext->getSkippedProperties() === []) {
			$object->setSkippedPropertiesContext(null);
		}
	}

}
