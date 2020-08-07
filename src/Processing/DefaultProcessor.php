<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Processing;

use Nette\Utils\ObjectHelpers;
use Nette\Utils\Validators;
use Orisai\Exceptions\Logic\InvalidArgument;
use Orisai\Exceptions\Logic\InvalidState;
use Orisai\ObjectMapper\Callbacks\AfterCallback;
use Orisai\ObjectMapper\Callbacks\BeforeCallback;
use Orisai\ObjectMapper\Callbacks\Callback;
use Orisai\ObjectMapper\Context\BaseFieldContext;
use Orisai\ObjectMapper\Context\FieldContext;
use Orisai\ObjectMapper\Context\FieldSetContext;
use Orisai\ObjectMapper\Context\PartiallyInitializedObjectContext;
use Orisai\ObjectMapper\Context\ProcessorRunContext;
use Orisai\ObjectMapper\Context\TypeContext;
use Orisai\ObjectMapper\Creation\ObjectCreator;
use Orisai\ObjectMapper\Exception\InvalidData;
use Orisai\ObjectMapper\Exception\ValueDoesNotMatch;
use Orisai\ObjectMapper\Meta\ArgsCreator;
use Orisai\ObjectMapper\Meta\ClassMeta;
use Orisai\ObjectMapper\Meta\MetaLoader;
use Orisai\ObjectMapper\Meta\PropertyMeta;
use Orisai\ObjectMapper\Meta\SharedMeta;
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
	 * @template T of ValueObject
	 * @param mixed $data
	 * @throws InvalidData
	 * @phpstan-param class-string<T> $class
	 * @phpstan-return T
	 */
	public function process($data, string $class, ?Options $options = null): ValueObject
	{
		$options ??= new Options();
		$type = $this->createStructureType($class);
		$holder = $this->createHolder($class);

		$fieldSetContext = $this->createFieldSetContext($options, $type, true);
		$runContext = $this->createProcessorRunContext($class, $holder);

		$processedData = $this->processDataInternal($data, $fieldSetContext, $runContext);

		$object = $holder->getInstance();
		$this->fillObject($object, $processedData, $data, $fieldSetContext, $runContext);

		return $object;
	}

	/**
	 * @param mixed $data
	 * @return array<int|string, mixed>
	 * @throws InvalidData
	 * @phpstan-param class-string<ValueObject> $class
	 */
	public function processWithoutInitialization($data, string $class, ?Options $options = null): array
	{
		$options ??= new Options();
		$type = $this->createStructureType($class);
		$holder = $this->createHolder($class);

		$fieldSetContext = $this->createFieldSetContext($options, $type, false);
		$runContext = $this->createProcessorRunContext($class, $holder);

		return $this->processDataInternal($data, $fieldSetContext, $runContext);
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
	protected function processDataInternal(
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
	 * @phpstan-param class-string<ValueObject> $class
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
	 * @phpstan-param class-string<ValueObject> $class
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

			throw InvalidData::create($type);
		}

		return $data;
	}

	/**
	 * @param int|string $fieldName
	 * @todo field->property name metadata
	 */
	protected function fieldNameToPropertyName($fieldName): string
	{
		return (string) $fieldName;
	}

	/**
	 * @return int|string
	 * @todo property->field name metadata
	 */
	protected function propertyNameToFieldName(string $propertyName)
	{
		if (Validators::isNumericInt($propertyName)) {
			return (int) $propertyName;
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
			throw InvalidData::create($type);
		}

		return $data;
	}

	/**
	 * @param array<int|string, mixed> $data
	 * @return array<int|string, mixed>
	 * @todo - implement LateProcessedModifier and properties skipping
	 */
	protected function handleSentFields(
		array $data,
		FieldSetContext $fieldSetContext,
		ProcessorRunContext $runContext
	): array
	{
		$type = $fieldSetContext->getType();

		$propertiesMeta = $runContext->getMeta()->getProperties();
		/** @var array<string> $propertyNames */
		$propertyNames = array_keys($propertiesMeta);

		// Processed data added to new variable to filter-out unexpected values
		$processedData = [];

		foreach ($data as $fieldName => $value) {
			// Skip invalid field
			if ($type->isFieldInvalid($fieldName)) {
				continue;
			}

			$propertyName = $this->fieldNameToPropertyName($fieldName);

			if (!isset($propertiesMeta[$propertyName])) {
				$hintedPropertyName = ObjectHelpers::getSuggestion($propertyNames, $propertyName);
				$type->overwriteInvalidField(
					$fieldName,
					new MessageType(sprintf(
						'Field is unknown%s',
						($hintedPropertyName !== null ? sprintf(', did you mean `%s`?', $this->propertyNameToFieldName($hintedPropertyName)) : '.'),
					)),
				);

				continue;
			}

			$propertyMeta = $propertiesMeta[$propertyName];
			$fieldContext = $this->createFieldContext($fieldSetContext, $propertyMeta, $fieldName, $propertyName);

			try {
				$processedData[$fieldName] = $this->processProperty(
					$value,
					$fieldContext,
					$runContext,
					$propertyMeta,
				);
			} catch (ValueDoesNotMatch | InvalidData $exception) {
				$type->overwriteInvalidField($fieldName, $exception->getInvalidType());
			}
		}

		return $processedData;
	}

	/**
	 * @param array<int|string, mixed> $data
	 * @return array<string>
	 */
	protected function findMissingProperties(array $data, ProcessorRunContext $runContext): array
	{
		return array_diff(
			array_keys($runContext->getMeta()->getProperties()),
			array_map(fn ($fieldName): string => $this->fieldNameToPropertyName($fieldName), array_keys($data)),
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
		$requireDefaultValues = $options->isRequireDefaultValues();
		$fillDefaultValues = $initializeObjects || $options->isPreFillDefaultValues();

		$skippedProperties = $this->getSkippedProperties($runContext);

		foreach ($missingProperties as $missingProperty) {
			if (in_array($missingProperty, $skippedProperties, true)) {
				continue;
			}

			$propertyMeta = $propertiesMeta[$missingProperty];
			$defaultMeta = $propertyMeta->getDefault();
			$missingField = $this->propertyNameToFieldName($missingProperty);

			if (!$requireDefaultValues && $defaultMeta->hasValue()) {
				// Add default value if defaults are not required and should be used
				if ($fillDefaultValues) {
					$data[$missingField] = $defaultMeta->getValue();
				}
			} elseif (is_a($propertyMeta->getRule()->getType(), StructureRule::class, true)) {
				// Try initialize structure from empty array when no data given
				// Structure in compound type is not supported
				try {
					$structureArgs = StructureArgs::fromArray($propertyMeta->getRule()->getArgs());
					$data[$missingField] = $initializeObjects
						? $this->process([], $structureArgs->type, $options)
						: $this->processWithoutInitialization([], $structureArgs->type, $options);
				} catch (InvalidData $exception) {
					$type->overwriteInvalidField($missingField, $exception->getInvalidType());
				}
			} elseif (!$type->isFieldInvalid($missingField)) {
				// Field is missing and have no default value, mark as invalid
				$propertyRuleMeta = $propertyMeta->getRule();
				$propertyRule = $this->ruleManager->getRule($propertyRuleMeta->getType());
				$type->overwriteInvalidField(
					$missingField,
					$propertyRule->createType(
						$this->createRuleArgsInst($propertyRule, $propertyRuleMeta),
						$this->getTypeContext(),
					),
				);
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
	 * @return array<mixed>
	 * @throws InvalidData
	 * @phpstan-param class-string<Callback> $callbackType
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
			$catchedType = $exception->getInvalidType();

			// User thrown type is not the actual type from FieldSetContext
			if ($catchedType !== $type) {
				$type->addError($catchedType);
			}

			throw InvalidData::create($type);
		}

		return $data;
	}

	/**
	 * @param mixed $data
	 * @param FieldContext|FieldSetContext $baseFieldContext
	 * @param ClassMeta|PropertyMeta $meta
	 * @return mixed
	 * @throws ValueDoesNotMatch
	 * @throws InvalidData
	 * @phpstan-param class-string<Callback> $callbackType
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
				$data = $callbackType::invoke($data, $this->createCallbackArgsInst($callbackType, $callback), $holder, $baseFieldContext);
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

		if ($options->isFillRawValues()) {
			$object->setRawValues($rawData);
		}

		foreach ($data as $fieldName => $value) {
			$propertyName = $this->fieldNameToPropertyName($fieldName);
			$object->$propertyName = $value;
		}

		$skippedProperties = $runContext->getSkippedProperties();

		if ($skippedProperties !== []) {
			$partial = new PartiallyInitializedObjectContext($type, $options);
			$object->setPartialContext($partial);

			foreach ($skippedProperties as $propertyName => $uninitializedPropertyContext) {
				$partial->addUninitializedProperty($propertyName, $uninitializedPropertyContext);
			}
		}
	}

	/**
	 * @phpstan-param class-string<ValueObject> $class
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
	public function processUninitializedProperties(
		array $properties,
		ValueObject $object,
		?Options $options = null
	): void
	{
		$class = get_class($object);

		if (!$object->hasPartialContext()) {
			throw InvalidState::create()
				->withMessage(sprintf(
					'Cannot initialize properties "%s" of instance of "%s" because object is already fully initialized',
					implode(', ', $properties),
					$class,
				));
		}

		$partial = $object->getPartialContext();

		$type = $partial->getType();
		$options ??= $partial->getOptions();
		$fieldSetContext = $this->createFieldSetContext($options, $type, true);
		$uninitialized = $partial->getUninitializedProperties();

		$holder = $this->createHolder($class, $object);
		$runContext = $this->createProcessorRunContext($class, $holder);
		$propertiesMeta = $this->metaLoader->load($class)->getProperties();

		foreach ($properties as $propertyName) {
			if (!array_key_exists($propertyName, $uninitialized)) {
				throw InvalidArgument::create()
					->withMessage(sprintf(
						'Cannot initialize property "%s" of instance of "%s" because it is already initialized or does not exist.',
						$propertyName,
						$class,
					));
			}

			$uninitializedPropertyContext = $uninitialized[$propertyName];
			$propertyMeta = $propertiesMeta[$propertyName];

			$fieldName = $uninitializedPropertyContext->getFieldName();
			$fieldContext = $this->createFieldContext($fieldSetContext, $propertyMeta, $fieldName, $propertyName);

			try {
				$object->$propertyName = $this->processProperty(
					$uninitializedPropertyContext->getValue(),
					$fieldContext,
					$runContext,
					$propertyMeta,
				);
				$partial->removeInitializedProperty($propertyName);
			} catch (ValueDoesNotMatch | InvalidData $exception) {
				$type->overwriteInvalidField($fieldName, $exception->getInvalidType());
			}
		}

		if ($type->hasInvalidFields()) {
			throw InvalidData::create($type);
		}

		// Object is fully initialized, remove partial context
		if ($partial->getUninitializedProperties() === []) {
			$object->setPartialContext(null);
		}
	}

}
