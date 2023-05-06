<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Processing;

use Nette\Utils\Helpers;
use Orisai\Exceptions\Logic\InvalidState;
use Orisai\ObjectMapper\Args\Args;
use Orisai\ObjectMapper\Callbacks\AfterCallback;
use Orisai\ObjectMapper\Callbacks\BeforeCallback;
use Orisai\ObjectMapper\Callbacks\Callback;
use Orisai\ObjectMapper\Context\BaseFieldContext;
use Orisai\ObjectMapper\Context\FieldContext;
use Orisai\ObjectMapper\Context\MappedObjectContext;
use Orisai\ObjectMapper\Context\ProcessorCallContext;
use Orisai\ObjectMapper\Context\SkippedFieldContext;
use Orisai\ObjectMapper\Context\SkippedFieldsContext;
use Orisai\ObjectMapper\Context\TypeContext;
use Orisai\ObjectMapper\Exception\InvalidData;
use Orisai\ObjectMapper\Exception\ValueDoesNotMatch;
use Orisai\ObjectMapper\MappedObject;
use Orisai\ObjectMapper\Meta\MetaLoader;
use Orisai\ObjectMapper\Meta\Runtime\ClassRuntimeMeta;
use Orisai\ObjectMapper\Meta\Runtime\FieldRuntimeMeta;
use Orisai\ObjectMapper\Meta\Runtime\NodeRuntimeMeta;
use Orisai\ObjectMapper\Meta\Runtime\RuntimeMeta;
use Orisai\ObjectMapper\Modifiers\SkippedModifier;
use Orisai\ObjectMapper\Rules\MappedObjectArgs;
use Orisai\ObjectMapper\Rules\MappedObjectRule;
use Orisai\ObjectMapper\Rules\RuleManager;
use Orisai\ObjectMapper\Types\MappedObjectType;
use Orisai\ObjectMapper\Types\MessageType;
use ReflectionProperty;
use stdClass;
use function array_diff;
use function array_key_exists;
use function array_keys;
use function array_map;
use function assert;
use function get_class;
use function implode;
use function in_array;
use function is_array;
use function sprintf;

final class DefaultProcessor implements Processor
{

	private MetaLoader $metaLoader;

	private RuleManager $ruleManager;

	private ObjectCreator $objectCreator;

	private SkippedFieldsContextMap $skippedMap;

	private RawValuesMap $rawValuesMap;

	/** @var array<class-string<MappedObject>, RuntimeMeta> */
	private array $metaCache = [];

	public function __construct(MetaLoader $metaLoader, RuleManager $ruleManager, ObjectCreator $objectCreator)
	{
		$this->metaLoader = $metaLoader;
		$this->ruleManager = $ruleManager;
		$this->objectCreator = $objectCreator;
		$this->skippedMap = new SkippedFieldsContextMap();
		$this->rawValuesMap = new RawValuesMap();
	}

	/**
	 * @param mixed $data
	 * @throws InvalidData
	 */
	public function process($data, string $class, ?Options $options = null): MappedObject
	{
		[
			$processedData,
			$holder,
			$mappedObjectContext,
			$callContext,
		] = $this->processBase($data, $class, $options, true);

		$object = $holder->getInstance();
		$this->fillObject($object, $processedData, $data, $mappedObjectContext, $callContext);

		return $object;
	}

	/**
	 * @param mixed                      $data
	 * @param class-string<MappedObject> $class
	 * @return array<int|string, mixed>
	 * @throws InvalidData
	 */
	public function processWithoutMapping($data, string $class, ?Options $options = null): array
	{
		[$processedData] = $this->processBase($data, $class, $options, false);

		return $processedData;
	}

	/**
	 * @template T of MappedObject
	 * @param mixed           $data
	 * @param class-string<T> $class
	 * @return array{array<mixed>, ObjectHolder<T>, MappedObjectContext, ProcessorCallContext<T>}
	 * @throws InvalidData
	 */
	private function processBase($data, string $class, ?Options $options, bool $initializeObjects): array
	{
		$options ??= new Options();
		$options = $options->withProcessedClass($class);
		$type = $this->createMappedObjectType($class, $options);
		$meta = $this->metaCache[$class] ??= $this->metaLoader->load($class);
		$holder = $this->createHolder($class, $meta->getClass());

		$mappedObjectContext = $this->createMappedObjectContext($options, $type, $initializeObjects);
		$callContext = $this->createProcessorRunContext($class, $meta, $holder);

		$processedData = $this->processData($data, $mappedObjectContext, $callContext);

		if ($options->getProcessedClasses() === [$class]) {
			$this->metaCache = [];
		}

		return [$processedData, $holder, $mappedObjectContext, $callContext];
	}

	private function createTypeContext(Options $options): TypeContext
	{
		return new TypeContext($this->metaLoader, $this->ruleManager, $options);
	}

	// /////////////// //
	// Base processing //
	// /////////////// //

	/**
	 * @param mixed                              $data
	 * @param ProcessorCallContext<MappedObject> $callContext
	 * @return array<int|string, mixed>
	 * @throws InvalidData
	 */
	private function processData(
		$data,
		MappedObjectContext $mappedObjectContext,
		ProcessorCallContext $callContext
	): array
	{
		$meta = $callContext->getMeta();
		$classMeta = $meta->getClass();

		if ($data instanceof stdClass) {
			$data = (array) $data;
		}

		$data = $this->handleClassCallbacks(
			$data,
			$mappedObjectContext,
			$callContext,
			$classMeta,
			BeforeCallback::class,
		);
		$data = $this->ensureDataProcessable($data, $mappedObjectContext);
		$data = $this->handleFields($data, $mappedObjectContext, $callContext);
		$data = $this->handleClassCallbacks(
			$data,
			$mappedObjectContext,
			$callContext,
			$classMeta,
			AfterCallback::class,
		);
		assert(is_array($data)); // After class callbacks are forced to return array

		return $data;
	}

	/**
	 * @param class-string<MappedObject> $class
	 */
	private function createMappedObjectType(string $class, Options $options): MappedObjectType
	{
		return $this->ruleManager->getRule(MappedObjectRule::class)->createType(
			new MappedObjectArgs($class),
			$this->createTypeContext($options),
		);
	}

	private function createMappedObjectContext(
		Options $options,
		MappedObjectType $type,
		bool $initializeObjects
	): MappedObjectContext
	{
		return new MappedObjectContext(
			$this->metaLoader,
			$this->ruleManager,
			$this,
			$options,
			$type,
			$initializeObjects,
		);
	}

	/**
	 * @template RC of MappedObject
	 * @param class-string<RC> $class
	 * @param ObjectHolder<RC> $holder
	 * @return ProcessorCallContext<RC>
	 */
	private function createProcessorRunContext(
		string $class,
		RuntimeMeta $meta,
		ObjectHolder $holder
	): ProcessorCallContext
	{
		return new ProcessorCallContext($class, $holder, $meta);
	}

	/**
	 * @param mixed $data
	 * @return array<mixed>
	 * @throws InvalidData
	 */
	private function ensureDataProcessable($data, MappedObjectContext $context): array
	{
		if (!is_array($data)) {
			$type = $context->getType();
			$type->markInvalid();

			throw InvalidData::create($type, Value::of($data));
		}

		return $data;
	}

	// /////////////////// //
	// Properties / Fields //
	// /////////////////// //

	/**
	 * @param array<int|string, mixed>           $data
	 * @param ProcessorCallContext<MappedObject> $callContext
	 * @return array<int|string, mixed>
	 * @throws InvalidData
	 */
	private function handleFields(
		array $data,
		MappedObjectContext $mappedObjectContext,
		ProcessorCallContext $callContext
	): array
	{
		$data = $this->handleSentFields($data, $mappedObjectContext, $callContext);
		$data = $this->handleMissingFields($data, $mappedObjectContext, $callContext);

		$type = $mappedObjectContext->getType();

		if ($type->hasInvalidFields()) {
			throw InvalidData::create($type, Value::none());
		}

		return $data;
	}

	/**
	 * @param array<int|string, mixed>           $data
	 * @param ProcessorCallContext<MappedObject> $callContext
	 * @return array<int|string, mixed>
	 */
	private function handleSentFields(
		array $data,
		MappedObjectContext $mappedObjectContext,
		ProcessorCallContext $callContext
	): array
	{
		$type = $mappedObjectContext->getType();
		$options = $mappedObjectContext->getOptions();

		$meta = $callContext->getMeta();
		$fieldsMeta = $meta->getFields();
		$fieldNames = array_keys($fieldsMeta);

		foreach ($data as $fieldName => $value) {
			// Skip invalid field
			if ($type->isFieldInvalid($fieldName)) {
				continue;
			}

			$fieldMeta = $fieldsMeta[$fieldName] ?? null;

			// Unknown field
			if ($fieldMeta === null) {
				// Remove field from data
				unset($data[$fieldName]);

				if ($options->isAllowUnknownFields()) {
					continue;
				}

				$hintedFieldName = Helpers::getSuggestion(
					array_map(static fn ($fieldName) => (string) $fieldName, $fieldNames),
					(string) $fieldName,
				);
				$hint = $hintedFieldName !== null ? sprintf(', did you mean `%s`?', $hintedFieldName) : '.';

				// Add error to type
				$type->overwriteInvalidField(
					$fieldName,
					ValueDoesNotMatch::create(
						new MessageType(sprintf('Field is unknown%s', $hint)),
						Value::of($value),
					),
				);

				continue;
			}

			$fieldContext = $this->createFieldContext(
				$mappedObjectContext,
				$fieldMeta,
				$fieldName,
				$fieldMeta->getProperty(),
			);

			// Skip skipped property
			if (
				$mappedObjectContext->shouldInitializeObjects()
				&& $fieldMeta->getModifier(SkippedModifier::class) !== null
			) {
				$callContext->addSkippedField(
					$fieldName,
					new SkippedFieldContext($fieldMeta->getProperty(), $value, false),
				);
				unset($data[$fieldName]);

				continue;
			}

			// Process field value with property rules
			try {
				$data[$fieldName] = $this->processProperty(
					$value,
					$fieldContext,
					$callContext,
					$fieldMeta,
				);
			} catch (ValueDoesNotMatch | InvalidData $exception) {
				$type->overwriteInvalidField($fieldName, $exception);
			}
		}

		return $data;
	}

	/**
	 * @param array<int|string, mixed>           $data
	 * @param ProcessorCallContext<MappedObject> $callContext
	 * @return array<int|string>
	 */
	private function findMissingFields(array $data, ProcessorCallContext $callContext): array
	{
		$meta = $callContext->getMeta();

		return array_diff(
			array_keys($meta->getFields()),
			array_keys($data),
		);
	}

	/**
	 * @param ProcessorCallContext<MappedObject> $callContext
	 * @return array<int|string>
	 */
	private function getSkippedFields(ProcessorCallContext $callContext): array
	{
		return array_keys($callContext->getSkippedFields());
	}

	/**
	 * @param array<int|string, mixed>           $data
	 * @param ProcessorCallContext<MappedObject> $callContext
	 * @return array<int|string, mixed>
	 */
	private function handleMissingFields(
		array $data,
		MappedObjectContext $mappedObjectContext,
		ProcessorCallContext $callContext
	): array
	{
		$type = $mappedObjectContext->getType();
		$options = $mappedObjectContext->getOptions();
		$initializeObjects = $mappedObjectContext->shouldInitializeObjects();

		$meta = $callContext->getMeta();
		$fieldsMeta = $meta->getFields();

		$requiredFields = $options->getRequiredFields();
		$fillDefaultValues = $initializeObjects || $options->isPrefillDefaultValues();

		$skippedFields = $this->getSkippedFields($callContext);

		foreach ($this->findMissingFields($data, $callContext) as $missingField) {
			// Skipped properties are not considered missing, they are just processed later
			if (in_array($missingField, $skippedFields, true)) {
				continue;
			}

			$fieldMeta = $fieldsMeta[$missingField];
			$defaultMeta = $fieldMeta->getDefault();

			if ($requiredFields === RequiredFields::nonDefault() && $defaultMeta->hasValue()) {
				// Add default value if defaults are not required and should be used
				// If VOs are initialized then values are always prefilled - user can work with them in after callback,
				//   and they are defined by VO anyway
				if ($fillDefaultValues) {
					$data[$missingField] = $defaultMeta->getValue();
				}
			} elseif ($requiredFields !== RequiredFields::none() && !$type->isFieldInvalid($missingField)) {
				// Field is missing and have no default value, mark as invalid
				$fieldRuleMeta = $fieldMeta->getRule();
				$fieldRule = $this->ruleManager->getRule($fieldRuleMeta->getType());
				$type->overwriteInvalidField(
					$missingField,
					ValueDoesNotMatch::create(
						$fieldRule->createType(
							$fieldRuleMeta->getArgs(),
							$this->createTypeContext($options),
						),
						Value::none(),
					),
				);
			}

			// Return skipped property separately
			if (
				array_key_exists($missingField, $data)
				&& $mappedObjectContext->shouldInitializeObjects()
				&& $fieldMeta->getModifier(SkippedModifier::class) !== null
			) {
				$callContext->addSkippedField(
					$missingField,
					new SkippedFieldContext(
						$fieldMeta->getProperty(),
						$data[$missingField],
						true,
					),
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
	private function createFieldContext(
		MappedObjectContext $mappedObjectContext,
		FieldRuntimeMeta $meta,
		$fieldName,
		ReflectionProperty $property
	): FieldContext
	{
		$parentType = $mappedObjectContext->getType();

		return new FieldContext(
			$this->metaLoader,
			$this->ruleManager,
			$this,
			$mappedObjectContext->getOptions()->createClone(),
			$parentType->getFields()[$fieldName],
			$meta->getDefault(),
			$mappedObjectContext->shouldInitializeObjects(),
			$fieldName,
			$property,
		);
	}

	/**
	 * @param mixed                              $value
	 * @param ProcessorCallContext<MappedObject> $callContext
	 * @return mixed
	 * @throws ValueDoesNotMatch
	 * @throws InvalidData
	 */
	private function processProperty(
		$value,
		FieldContext $fieldContext,
		ProcessorCallContext $callContext,
		FieldRuntimeMeta $meta
	)
	{
		$value = $this->applyCallbacks($value, $fieldContext, $callContext, $meta, BeforeCallback::class);
		$value = $this->processPropertyRules($value, $fieldContext, $meta);
		$value = $this->applyCallbacks($value, $fieldContext, $callContext, $meta, AfterCallback::class);

		return $value;
	}

	/**
	 * @param mixed $value
	 * @return mixed
	 * @throws ValueDoesNotMatch
	 * @throws InvalidData
	 */
	private function processPropertyRules($value, FieldContext $fieldContext, FieldRuntimeMeta $meta)
	{
		$ruleMeta = $meta->getRule();
		$rule = $this->ruleManager->getRule($ruleMeta->getType());

		return $rule->processValue(
			$value,
			$ruleMeta->getArgs(),
			$fieldContext,
		);
	}

	// ///////// //
	// Callbacks //
	// ///////// //

	/**
	 * @param mixed                              $data
	 * @param ProcessorCallContext<MappedObject> $callContext
	 * @param class-string<Callback<Args>>       $callbackType
	 * @return mixed
	 * @throws InvalidData
	 */
	private function handleClassCallbacks(
		$data,
		MappedObjectContext $mappedObjectContext,
		ProcessorCallContext $callContext,
		ClassRuntimeMeta $meta,
		string $callbackType
	)
	{
		$type = $mappedObjectContext->getType();

		try {
			$data = $this->applyCallbacks($data, $mappedObjectContext, $callContext, $meta, $callbackType);
		} catch (ValueDoesNotMatch | InvalidData $exception) {
			$caughtType = $exception->getType();

			// User thrown type is not the actual type from MappedObjectContext
			if ($caughtType !== $type) {
				$type->addError($exception);

				throw InvalidData::create($type, Value::none());
			}

			throw InvalidData::create($type, $exception->getValue());
		}

		return $data;
	}

	/**
	 * @param mixed                              $data
	 * @param FieldContext|MappedObjectContext   $baseFieldContext
	 * @param ProcessorCallContext<MappedObject> $callContext
	 * @param ClassRuntimeMeta|FieldRuntimeMeta  $meta
	 * @param class-string<Callback<Args>>       $callbackType
	 * @return mixed
	 * @throws ValueDoesNotMatch
	 * @throws InvalidData
	 */
	private function applyCallbacks(
		$data,
		BaseFieldContext $baseFieldContext,
		ProcessorCallContext $callContext,
		NodeRuntimeMeta $meta,
		string $callbackType
	)
	{
		$holder = $callContext->getObjectHolder();

		foreach ($meta->getCallbacks() as $callback) {
			if ($callback->getType() === $callbackType) {
				$data = $callbackType::invoke(
					$data,
					$callback->getArgs(),
					$holder,
					$baseFieldContext,
					$callback->getDeclaringClass(),
				);
			}
		}

		return $data;
	}


	// ///////////// //
	// Mapped Object //
	// ///////////// //

	/**
	 * @param array<int|string, mixed>           $data
	 * @param mixed                              $rawData
	 * @param ProcessorCallContext<MappedObject> $callContext
	 */
	private function fillObject(
		MappedObject $object,
		array $data,
		$rawData,
		MappedObjectContext $mappedObjectContext,
		ProcessorCallContext $callContext
	): void
	{
		$type = $mappedObjectContext->getType();
		$options = $mappedObjectContext->getOptions();
		$meta = $callContext->getMeta();

		// Set raw data
		if ($options->isTrackRawValues()) {
			$this->rawValuesMap->setRawValues($object, $rawData);
		}

		// Reset mapped properties state
		$fieldsMeta = $meta->getFields();
		foreach ($fieldsMeta as $fieldMeta) {
			$this->objectUnset($object, $fieldMeta->getProperty());
		}

		// Set processed data
		foreach ($data as $fieldName => $value) {
			$this->objectSet($object, $fieldsMeta[$fieldName]->getProperty(), $value);
		}

		// Set skipped properties
		$skippedFields = $callContext->getSkippedFields();
		if ($skippedFields !== []) {
			$skippedContext = new SkippedFieldsContext($type, $options);
			$this->skippedMap->setSkippedFieldsContext($object, $skippedContext);

			foreach ($skippedFields as $fieldName => $skippedFieldContext) {
				$skippedContext->addSkippedField($fieldName, $skippedFieldContext);
			}
		}
	}

	/**
	 * @template H of MappedObject
	 * @param class-string<H> $class
	 * @param H|null          $object
	 * @return ObjectHolder<H>
	 */
	private function createHolder(string $class, ClassRuntimeMeta $meta, ?MappedObject $object = null): ObjectHolder
	{
		return new ObjectHolder($this->objectCreator, $meta, $class, $object);
	}

	public function getRawValues(MappedObject $object)
	{
		return $this->rawValuesMap->getRawValues($object);
	}

	/**
	 * @param mixed $value
	 */
	private function objectSet(MappedObject $object, ReflectionProperty $property, $value): void
	{
		$declaringClass = $property->getDeclaringClass();
		$name = $property->getName();

		// phpcs:disable SlevomatCodingStandard.Functions.StaticClosure
		(fn () => $object->$name = $value)
			->bindTo($object, $declaringClass->getName())();
		// phpcs:enable
	}

	private function objectUnset(MappedObject $object, ReflectionProperty $property): void
	{
		$declaringClass = $property->getDeclaringClass();
		$name = $property->getName();

		// phpcs:disable SlevomatCodingStandard.Functions.StaticClosure
		(function () use ($object, $name): void {
			unset($object->$name);
		})->bindTo($object, $declaringClass->getName())();
		// phpcs:enable
	}

	// ////////////// //
	// Late processing //
	// ////////////// //

	public function processSkippedFields(
		array $fields,
		MappedObject $object,
		?Options $options = null
	): void
	{
		$class = get_class($object);

		// Object has no skipped properties
		if (!$this->skippedMap->hasSkippedFieldsContext($object)) {
			throw InvalidState::create()
				->withMessage(sprintf(
					'Cannot initialize fields "%s" of "%s" instance because it has no skipped fields.',
					implode(', ', $fields),
					$class,
				));
		}

		$skippedFieldsContext = $this->skippedMap->getSkippedFieldsContext($object);

		$type = $skippedFieldsContext->getType();
		$options ??= $skippedFieldsContext->getOptions();
		$mappedObjectContext = $this->createMappedObjectContext($options, $type, true);
		$skippedFields = $skippedFieldsContext->getSkippedFields();

		$meta = $this->metaLoader->load($class);
		$holder = $this->createHolder($class, $meta->getClass(), $object);
		$callContext = $this->createProcessorRunContext($class, $meta, $holder);
		$fieldsMeta = $meta->getFields();

		foreach ($fields as $fieldName) {
			// Property is initialized or does not exist
			if (!array_key_exists($fieldName, $skippedFields)) {
				throw InvalidState::create()
					->withMessage(sprintf(
						'Cannot initialize field "%s" of "%s" instance because it is already initialized or does not exist.',
						$fieldName,
						$class,
					));
			}

			$skippedFieldContext = $skippedFields[$fieldName];
			$fieldMeta = $fieldsMeta[$fieldName];
			$fieldContext = $this->createFieldContext(
				$mappedObjectContext,
				$fieldMeta,
				$fieldName,
				$skippedFieldContext->getProperty(),
			);

			// Process field value with property rules
			if ($skippedFieldContext->isDefault()) {
				$processed = $skippedFieldContext->getValue();
			} else {
				try {
					$processed = $this->processProperty(
						$skippedFieldContext->getValue(),
						$fieldContext,
						$callContext,
						$fieldMeta,
					);
				} catch (ValueDoesNotMatch | InvalidData $exception) {
					$type->overwriteInvalidField($fieldName, $exception);

					continue;
				}
			}

			$this->objectSet($object, $fieldMeta->getProperty(), $processed);
			$skippedFieldsContext->removeSkippedField($fieldName);
		}

		// If any of fields is invalid, throw error
		if ($type->hasInvalidFields()) {
			throw InvalidData::create($type, Value::none());
		}

		// Object is fully initialized, remove partial context
		if ($skippedFieldsContext->getSkippedFields() === []) {
			$this->skippedMap->setSkippedFieldsContext($object, null);
		}
	}

}
