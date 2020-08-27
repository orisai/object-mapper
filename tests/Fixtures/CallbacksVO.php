<?php declare(strict_types = 1);

namespace Tests\Orisai\ObjectMapper\Fixtures;

use Orisai\Exceptions\Logic\InvalidState;
use Orisai\ObjectMapper\Annotation\Callback;
use Orisai\ObjectMapper\Annotation\Expect;
use Orisai\ObjectMapper\Callbacks\CallbackRuntime;
use Orisai\ObjectMapper\Context\FieldContext;
use Orisai\ObjectMapper\Context\FieldSetContext;
use Orisai\ObjectMapper\Exception\InvalidData;
use Orisai\ObjectMapper\ValueObject;
use function array_key_exists;

/**
 * @Callback\Before(method="beforeClass", runtime=CallbackRuntime::ALWAYS)
 * @Callback\After(method="afterClass", runtime=CallbackRuntime::ALWAYS)
 */
final class CallbacksVO extends ValueObject
{

	public const STRUCTURE_CLASS = 'structure_class';

	private string $constructorGivenValue;

	/**
	 * @var array<string, array<mixed>>
	 * @Expect\ArrayOf(
	 *     keyRule=@Expect\StringValue(),
	 *     itemRule=@Expect\ArrayOf(
	 *         @Expect\MixedValue(),
	 *     ),
	 * )
	 * @Callback\After(method="afterArrayInitialization", runtime=CallbackRuntime::INITIALIZATION)
	 * @Callback\After(method="afterArrayProcessing", runtime=CallbackRuntime::PROCESSING)
	 */
	public array $array;

	/**
	 * @Expect\ArrayOf(
	 *     @Expect\MixedValue()
	 * )
	 * @Callback\After(method="afterStructure", runtime=CallbackRuntime::ALWAYS)
	 */
	public ValueObject $structure;

	/** @Expect\StringValue() */
	public string $overridenDefaultValue = 'defaultValue_before_override';

	/**
	 * @Expect\StringValue()
	 * @Callback\Before(method="beforeImmutableDefaultValue", runtime=CallbackRuntime::ALWAYS)
	 * @Callback\Before(method="afterImmutableDefaultValue", runtime=CallbackRuntime::ALWAYS)
	 */
	public string $immutableDefaultValue = 'defaultValue_immutable';

	/** @Expect\StringValue() */
	public string $requiredValue;

	/**
	 * @Expect\StringValue()
	 * @Callback\Before(method="beforeCallbackSetValue", runtime=CallbackRuntime::ALWAYS)
	 * @Callback\After(method="afterCallbackSetValue", runtime=CallbackRuntime::ALWAYS)
	 */
	public string $callbackSetValue;

	public function __construct()
	{
		$this->constructorGivenValue = 'givenByConstructor';
	}

	/**
	 * @param array<mixed> $data
	 * @return array<mixed>
	 */
	public static function beforeClass(array $data, FieldSetContext $context): array
	{
		$data['array']['beforeClassCallback'][] = $context->isInitializeObjects();

		// Set default value, processor don't know it's going to be structure and thinks value is required
		//TODO - pravidlo na inicializaci z contextu? při inicializaci chceme vědět, že půjde o strukturu a ideálně i co nejpřesněji o jakou
		if (!array_key_exists('structure', $data)) {
			$data['structure'] = [];
		}

		// Override default and required value to ensure data set before validation pass even if they were not sent by user
		// Note: if we define 'before' callback for 'requiredValue' and value not available by moment of callback call then callback is not called at all
		$data['overridenDefaultValue'] = 'overriddenValue';
		$data['requiredValue'] = 'overriddenValue';

		return $data;
	}

	/**
	 * @param array<mixed> $data
	 * @return array<mixed>
	 */
	public static function afterClass(array $data, FieldSetContext $context): array
	{
		$data['array']['afterClassCallback'][] = $context->isInitializeObjects();

		if ($context->isInitializeObjects() && !$data['structure'] instanceof ValueObject) {
			throw InvalidState::create()
				->withMessage('Instance should be initialized by that moment');
		}

		if (!$context->isInitializeObjects() && $data['structure'] instanceof ValueObject) {
			throw InvalidState::create()
				->withMessage('Instance should not be initialized, context is not set to initialize object');
		}

		return $data;
	}

	/**
	 * @param array<mixed> $array
	 * @return array<mixed>
	 */
	public static function afterArrayProcessing(array $array, FieldContext $context): array
	{
		$array['afterArrayProcessingCallback'][] = $context->isInitializeObjects();

		return $array;
	}

	/**
	 * @param array<mixed> $array
	 * @return array<mixed>
	 */
	public static function afterArrayInitialization(array $array, FieldContext $context): array
	{
		$array['afterArrayInitializationCallback'][] = $context->isInitializeObjects();

		return $array;
	}

	/**
	 * @param array<mixed> $structure
	 * @return ValueObject|array<mixed>
	 * @throws InvalidData
	 */
	public static function afterStructure(array $structure, FieldContext $context)
	{
		$processor = $context->getProcessor();
		$options = $context->getOptions();
		$class = $options->getDynamicContext(self::class)[self::STRUCTURE_CLASS];

		return $context->isInitializeObjects()
			? $processor->process($structure, $class, $options)
			: $processor->processWithoutInitialization($structure, $class, $options);
	}

	public static function beforeImmutableDefaultValue(): void
	{
		throw InvalidState::create()
			->withMessage('Dont set "immutableDefaultValue". I am here to test before callback is not called when default value is used.');
	}

	public static function afterImmutableDefaultValue(): void
	{
		throw InvalidState::create()
			->withMessage('Dont set "immutableDefaultValue". I am here to test after callback is not called when default value is used.');
	}

	public function beforeCallbackSetValue(): string
	{
		// Set value from constructor
		return $this->constructorGivenValue;
	}

	public function afterCallbackSetValue(string $value): void
	{
		// Does not return, callback return value should not override value
	}

}
