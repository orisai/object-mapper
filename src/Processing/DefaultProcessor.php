<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Processing;

use Nette\Utils\Helpers;
use Orisai\ObjectMapper\Args\Args;
use Orisai\ObjectMapper\Callbacks\AfterCallback;
use Orisai\ObjectMapper\Callbacks\BeforeCallback;
use Orisai\ObjectMapper\Callbacks\Callback;
use Orisai\ObjectMapper\Callbacks\Context\CallbackBaseContext;
use Orisai\ObjectMapper\Callbacks\Context\FieldContext;
use Orisai\ObjectMapper\Callbacks\Context\ObjectContext;
use Orisai\ObjectMapper\Exception\InvalidData;
use Orisai\ObjectMapper\Exception\ValueDoesNotMatch;
use Orisai\ObjectMapper\MappedObject;
use Orisai\ObjectMapper\Meta\MetaLoader;
use Orisai\ObjectMapper\Meta\Runtime\ClassRuntimeMeta;
use Orisai\ObjectMapper\Meta\Runtime\FieldRuntimeMeta;
use Orisai\ObjectMapper\Meta\Runtime\NodeRuntimeMeta;
use Orisai\ObjectMapper\Meta\Runtime\RuntimeMeta;
use Orisai\ObjectMapper\Processing\Context\DynamicContext;
use Orisai\ObjectMapper\Processing\Context\ProcessorCallContext;
use Orisai\ObjectMapper\Processing\Context\PropertyContext;
use Orisai\ObjectMapper\Processing\Context\ServicesContext;
use Orisai\ObjectMapper\Rules\MappedObjectArgs;
use Orisai\ObjectMapper\Rules\MappedObjectRule;
use Orisai\ObjectMapper\Rules\RuleManager;
use Orisai\ObjectMapper\Types\MappedObjectType;
use Orisai\ObjectMapper\Types\MessageType;
use Orisai\ObjectMapper\Types\Type;
use ReflectionProperty;
use function array_key_exists;
use function array_keys;
use function array_map;
use function assert;
use function is_array;
use const PHP_VERSION_ID;

final class DefaultProcessor implements Processor
{

	private MetaLoader $metaLoader;

	private RuleManager $ruleManager;

	private ObjectCreator $objectCreator;

	private RawValuesMap $rawValuesMap;

	private ServicesContext $services;

	/** @var array<class-string<MappedObject>, RuntimeMeta> */
	private array $metaCache = [];

	/** @var array<class-string, array<string, PropertyContext>> */
	private array $propertyContextCache = [];

	public function __construct(MetaLoader $metaLoader, RuleManager $ruleManager, ObjectCreator $objectCreator)
	{
		$this->metaLoader = $metaLoader;
		$this->ruleManager = $ruleManager;
		$this->objectCreator = $objectCreator;
		$this->rawValuesMap = new RawValuesMap();
		$this->services = new ServicesContext($metaLoader, $ruleManager, $this);
	}

	public function reset(): void
	{
		$this->metaCache = [];
		$this->propertyContextCache = [];
	}

	/**
	 * @param mixed $data
	 * @throws InvalidData
	 */
	public function process($data, string $class, ?Options $options = null): MappedObject
	{
		[
			$processedData,
			$call,
			$dynamic,
		] = $this->processBase($data, $class, $options, true);

		$object = $call->getObjectHolder()->getInstance();
		$this->fillObject($object, $processedData, $data, $call, $dynamic);

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
	 * @return array{array<mixed>, ProcessorCallContext<T>, DynamicContext}
	 * @throws InvalidData
	 */
	private function processBase($data, string $class, ?Options $options, bool $initializeObjects): array
	{
		$meta = $this->metaCache[$class] ??= $this->metaLoader->load($class);

		$options ??= new Options();

		$dynamic = new DynamicContext($options, $initializeObjects);
		$call = new ProcessorCallContext(
			new ObjectHolder($this->objectCreator, $class, $meta->class),
			$meta,
			fn (): MappedObjectType => $this->createMappedObjectType($class, $dynamic),
		);

		$processedData = $this->processData($data, $call, $dynamic);

		return [$processedData, $call, $dynamic];
	}

	// /////////////// //
	// Base processing //
	// /////////////// //

	/**
	 * @param mixed                              $data
	 * @param ProcessorCallContext<MappedObject> $call
	 * @return array<int|string, mixed>
	 * @throws InvalidData
	 */
	private function processData(
		$data,
		ProcessorCallContext $call,
		DynamicContext $dynamic
	): array
	{
		$meta = $call->getMeta();
		$classMeta = $meta->class;

		if ($classMeta->callbacks !== []) {
			$callbackContext = new ObjectContext($this->services, $dynamic, $call);

			$data = $this->handleClassCallbacks(
				$data,
				$call,
				$callbackContext,
				$classMeta,
				BeforeCallback::class,
			);
		}

		$data = $this->ensureDataProcessable($data, $call);
		$data = $this->handleFields($data, $call, $dynamic);

		if (isset($callbackContext)) {
			$data = $this->handleClassCallbacks(
				$data,
				$call,
				$callbackContext,
				$classMeta,
				AfterCallback::class,
			);
			assert(is_array($data)); // After class callbacks are forced to return array
		}

		return $data;
	}

	/**
	 * @param class-string<MappedObject> $class
	 */
	private function createMappedObjectType(string $class, DynamicContext $dynamic): MappedObjectType
	{
		return $this->ruleManager->getRule(MappedObjectRule::class)->createType(
			new MappedObjectArgs($class),
			$this->services,
			$dynamic,
		);
	}

	/**
	 * @param mixed $data
	 * @param ProcessorCallContext<MappedObject> $context
	 * @return array<mixed>
	 * @throws InvalidData
	 */
	private function ensureDataProcessable($data, ProcessorCallContext $context): array
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
	 * @param ProcessorCallContext<MappedObject> $call
	 * @return array<int|string, mixed>
	 * @throws InvalidData
	 */
	private function handleFields(
		array $data,
		ProcessorCallContext $call,
		DynamicContext $dynamic
	): array
	{
		$meta = $call->getMeta();
		$fieldsMeta = $meta->fields;

		$data = $this->handleSentFields($data, $call, $dynamic, $fieldsMeta);
		$data = $this->handleMissingFields($data, $call, $dynamic, $fieldsMeta);

		$type = $call->getTypeIfInitialized();

		if ($type !== null && $type->hasInvalidFields()) {
			throw InvalidData::create($type, Value::none());
		}

		return $data;
	}

	/**
	 * @param array<int|string, mixed>           $data
	 * @param ProcessorCallContext<MappedObject> $call
	 * @param array<int|string, FieldRuntimeMeta> $fieldsMeta
	 * @param-out array<int|string, FieldRuntimeMeta> $fieldsMeta
	 * @return array<int|string, mixed>
	 */
	private function handleSentFields(
		array $data,
		ProcessorCallContext $call,
		DynamicContext $dynamic,
		array &$fieldsMeta
	): array
	{
		$type = null;
		$options = $dynamic->getOptions();

		$hintedFieldNames = null;

		foreach ($data as $fieldName => $value) {
			// Skip invalid field
			if ($type !== null && $type->isFieldInvalid($fieldName)) {
				unset($fieldsMeta[$fieldName]); // Remaining fields are handled as missing

				continue;
			}

			$fieldMeta = $fieldsMeta[$fieldName] ?? null;
			unset($fieldsMeta[$fieldName]); // Remaining fields are handled as missing

			// Unknown field
			if ($fieldMeta === null) {
				// Remove field from data
				unset($data[$fieldName]);

				if ($options->isAllowUnknownFields()) {
					continue;
				}

				$hintedFieldName = Helpers::getSuggestion(
					$hintedFieldNames ??= array_map(
						static fn ($fieldName) => (string) $fieldName,
						array_keys($fieldsMeta),
					),
					(string) $fieldName,
				);
				$hint = $hintedFieldName !== null && !array_key_exists($hintedFieldName, $data)
					? ", did you mean '$hintedFieldName'?"
					: '.';

				// Add error to type
				$type ??= $call->getType();
				$type->overwriteInvalidField(
					$fieldName,
					ValueDoesNotMatch::create(
						new MessageType("Field is unknown$hint"),
						Value::of($value),
					),
				);

				continue;
			}

			$property = $fieldMeta->property;
			$className = $property->getDeclaringClass()->getName();
			$propertyName = $property->getName();
			$propertyContext = $this->propertyContextCache[$className][$propertyName]
				?? (
				$this->propertyContextCache[$className][$propertyName] = new PropertyContext(
					$fieldMeta->default,
					$fieldMeta->property,
					$fieldName,
				));

			// Process field value with property rules
			try {
				$data[$fieldName] = $this->processProperty(
					$value,
					$propertyContext,
					$dynamic,
					$call,
					$fieldMeta,
				);
			} catch (ValueDoesNotMatch | InvalidData $exception) {
				$type ??= $call->getType();
				$type->overwriteInvalidField($fieldName, $exception);
			}
		}

		return $data;
	}

	/**
	 * @param array<int|string, mixed>           $data
	 * @param ProcessorCallContext<MappedObject> $call
	 * @param array<int|string, FieldRuntimeMeta> $fieldsMeta
	 * @return array<int|string, mixed>
	 */
	private function handleMissingFields(
		array $data,
		ProcessorCallContext $call,
		DynamicContext $dynamic,
		array $fieldsMeta
	): array
	{
		$type = null;
		$options = $dynamic->getOptions();
		$initializeObjects = $dynamic->shouldInitializeObjects();

		$requiredFields = $options->getRequiredFields();
		$fillDefaultValues = $initializeObjects || $options->isPrefillDefaultValues();

		// $fieldsMeta contains only missing fields at this point
		foreach ($fieldsMeta as $fieldName => $fieldMeta) {
			$fieldMeta = $fieldsMeta[$fieldName];
			$defaultMeta = $fieldMeta->default;

			if ($requiredFields === RequiredFields::nonDefault() && $defaultMeta->hasValue()) {
				// Add default value if defaults are not required and should be used
				// If VOs are initialized then values are always prefilled - user can work with them in after callback,
				//   and they are defined by VO anyway
				if ($fillDefaultValues) {
					$data[$fieldName] = $defaultMeta->getValue();
				}
			} elseif (
				$requiredFields !== RequiredFields::none()
				&& ($type === null || !$type->isFieldInvalid($fieldName))
			) {
				// Field is missing and have no default value, mark as invalid
				$fieldRuleMeta = $fieldMeta->rule;
				$fieldRule = $this->ruleManager->getRule($fieldRuleMeta->type);
				$type ??= $call->getType();
				$type->overwriteInvalidField(
					$fieldName,
					ValueDoesNotMatch::create(
						$fieldRule->createType(
							$fieldRuleMeta->args,
							$this->services,
							$dynamic,
						),
						Value::none(),
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
	 * @param mixed                              $value
	 * @param ProcessorCallContext<MappedObject> $call
	 * @return mixed
	 * @throws ValueDoesNotMatch
	 * @throws InvalidData
	 */
	private function processProperty(
		$value,
		PropertyContext $property,
		DynamicContext $dynamic,
		ProcessorCallContext $call,
		FieldRuntimeMeta $meta
	)
	{
		if ($meta->callbacks !== []) {
			$callbackContext = new FieldContext(
				$this->services,
				$dynamic,
				$property,
				static fn (): Type => $call->getType()->getField($property->getFieldName()),
			);

			$value = $this->applyCallbacks($value, $callbackContext, $call, $meta, BeforeCallback::class);
		}

		$value = $this->processPropertyRules($value, $property, $dynamic, $meta);

		if (isset($callbackContext)) {
			$value = $this->applyCallbacks($value, $callbackContext, $call, $meta, AfterCallback::class);
		}

		return $value;
	}

	/**
	 * @param mixed $value
	 * @return mixed
	 * @throws ValueDoesNotMatch
	 * @throws InvalidData
	 */
	private function processPropertyRules(
		$value,
		PropertyContext $property,
		DynamicContext $dynamic,
		FieldRuntimeMeta $meta
	)
	{
		$ruleMeta = $meta->rule;
		$rule = $this->ruleManager->getRule($ruleMeta->type);

		return $rule->processValue(
			$value,
			$ruleMeta->args,
			$this->services,
			$property,
			$dynamic,
		);
	}

	// ///////// //
	// Callbacks //
	// ///////// //

	/**
	 * @param mixed                              $data
	 * @param ProcessorCallContext<MappedObject> $call
	 * @param class-string<Callback<Args>>       $callbackType
	 * @return mixed
	 * @throws InvalidData
	 */
	private function handleClassCallbacks(
		$data,
		ProcessorCallContext $call,
		ObjectContext $callbackContext,
		ClassRuntimeMeta $meta,
		string $callbackType
	)
	{
		try {
			$data = $this->applyCallbacks($data, $callbackContext, $call, $meta, $callbackType);
		} catch (ValueDoesNotMatch | InvalidData $exception) {
			$type = $callbackContext->getType();
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
	 * @param mixed $data
	 * @param ObjectContext|FieldContext $callbackContext
	 * @param ProcessorCallContext<MappedObject> $call
	 * @param ClassRuntimeMeta|FieldRuntimeMeta $meta
	 * @param class-string<Callback<Args>> $callbackType
	 * @return mixed
	 * @throws ValueDoesNotMatch
	 * @throws InvalidData
	 */
	private function applyCallbacks(
		$data,
		CallbackBaseContext $callbackContext,
		ProcessorCallContext $call,
		NodeRuntimeMeta $meta,
		string $callbackType
	)
	{
		$holder = $call->getObjectHolder();

		foreach ($meta->getCallbacksByType($callbackType) as $callback) {
			$data = $callbackType::invoke(
				$data,
				$callback->args,
				$holder,
				$callbackContext,
				$callback->declaringClass,
			);
		}

		return $data;
	}


	// ///////////// //
	// Mapped Object //
	// ///////////// //

	/**
	 * @param array<int|string, mixed>           $data
	 * @param mixed                              $rawData
	 * @param ProcessorCallContext<MappedObject> $call
	 */
	private function fillObject(
		MappedObject $object,
		array $data,
		$rawData,
		ProcessorCallContext $call,
		DynamicContext $dynamic
	): void
	{
		$options = $dynamic->getOptions();
		$meta = $call->getMeta();

		// Set raw data
		if ($options->isTrackRawValues()) {
			$this->rawValuesMap->setRawValues($object, $rawData);
		}

		$fieldsMeta = $meta->fields;

		if ($dynamic->getOptions()->getRequiredFields() === RequiredFields::none()) {
			// Reset mapped properties state
			foreach ($fieldsMeta as $fieldMeta) {
				$this->objectUnset($object, $fieldMeta->property);
			}
		}

		// Set processed data
		foreach ($data as $fieldName => $value) {
			$this->objectSet($object, $fieldsMeta[$fieldName]->property, $value);
		}
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

		if ($property->isPublic() && (PHP_VERSION_ID < 8_01_00 || !$property->isReadOnly())) {
			$object->$name = $value;
		} else {
			// phpcs:disable SlevomatCodingStandard.Functions.StaticClosure
			(fn () => $object->$name = $value)
				->bindTo($object, $declaringClass->getName())();
			// phpcs:enable
		}
	}

	private function objectUnset(MappedObject $object, ReflectionProperty $property): void
	{
		$declaringClass = $property->getDeclaringClass();
		$name = $property->getName();

		if (
			$property->isInitialized($object)
			&& $property->isPublic()
			&& (PHP_VERSION_ID < 8_01_00 || !$property->isReadOnly())
		) {
			unset($object->$name);
		} else {
			// phpcs:disable SlevomatCodingStandard.Functions.StaticClosure
			(function () use ($object, $name): void {
				unset($object->$name);
			})->bindTo($object, $declaringClass->getName())();
			// phpcs:enable
		}
	}

}
