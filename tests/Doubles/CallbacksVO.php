<?php declare(strict_types = 1);

namespace Tests\Orisai\ObjectMapper\Doubles;

use Orisai\Exceptions\Logic\InvalidState;
use Orisai\ObjectMapper\Attributes\Callbacks\After;
use Orisai\ObjectMapper\Attributes\Callbacks\Before;
use Orisai\ObjectMapper\Attributes\Expect\ArrayOf;
use Orisai\ObjectMapper\Attributes\Expect\MixedValue;
use Orisai\ObjectMapper\Attributes\Expect\StringValue;
use Orisai\ObjectMapper\Callbacks\CallbackRuntime;
use Orisai\ObjectMapper\Context\FieldContext;
use Orisai\ObjectMapper\Context\FieldSetContext;
use Orisai\ObjectMapper\Exception\InvalidData;
use Orisai\ObjectMapper\MappedObject;
use function array_key_exists;
use function is_array;

/**
 * @Before(method="beforeClass", runtime=CallbackRuntime::ALWAYS)
 * @After(method="afterClass", runtime=CallbackRuntime::ALWAYS)
 */
final class CallbacksVO extends MappedObject
{

	private string $constructorGivenValue;

	/**
	 * @var array<string, array<mixed>>
	 *
	 * @ArrayOf(
	 *     key=@StringValue(),
	 *     item=@ArrayOf(
	 *         @MixedValue(),
	 *     ),
	 * )
	 * @After(method="afterArrayInitialization", runtime=CallbackRuntime::WITH_MAPPING)
	 * @After(method="afterArrayProcessing", runtime=CallbackRuntime::WITHOUT_MAPPING)
	 */
	public array $array;

	/**
	 * @ArrayOf(
	 *     @MixedValue()
	 * )
	 * @After(method="afterStructure", runtime=CallbackRuntime::ALWAYS)
	 */
	public MappedObject $structure;

	/** @StringValue() */
	public string $overriddenDefaultValue = 'defaultValue_before_override';

	/**
	 * @StringValue()
	 * @Before(method="beforeImmutableDefaultValue", runtime=CallbackRuntime::ALWAYS)
	 * @Before(method="afterImmutableDefaultValue", runtime=CallbackRuntime::ALWAYS)
	 */
	public string $immutableDefaultValue = 'defaultValue_immutable';

	/** @StringValue() */
	public string $requiredValue;

	/**
	 * @StringValue()
	 * @Before(method="beforeCallbackSetValue", runtime=CallbackRuntime::ALWAYS)
	 * @After(method="afterCallbackSetValue", runtime=CallbackRuntime::ALWAYS)
	 */
	public string $callbackSetValue;

	public function __construct()
	{
		$this->constructorGivenValue = 'givenByConstructor';
	}

	/**
	 * @param mixed $data
	 * @return mixed
	 */
	public static function beforeClass($data, FieldSetContext $context)
	{
		if (!is_array($data)) {
			return $data;
		}

		$data['array']['beforeClassCallback'][] = $context->shouldMapDataToObjects();

		// Set default value, processor don't know it's going to be structure and thinks value is required
		//TODO - pravidlo na inicializaci z contextu? při inicializaci chceme vědět, že půjde o strukturu a ideálně i co nejpřesněji o jakou
		if (!array_key_exists('structure', $data)) {
			$data['structure'] = [];
		}

		// Override default and required value to ensure data set before validation pass even if they were not sent by user
		// Note: if we define 'before' callback for 'requiredValue' and value not available by moment of callback
		// 		 call then callback is not called at all
		$data['overriddenDefaultValue'] = 'overriddenValue';
		$data['requiredValue'] = 'overriddenValue';

		return $data;
	}

	/**
	 * @param array<mixed> $data
	 * @return array<mixed>
	 */
	public static function afterClass(array $data, FieldSetContext $context): array
	{
		$data['array']['afterClassCallback'][] = $context->shouldMapDataToObjects();

		if ($context->shouldMapDataToObjects() && !$data['structure'] instanceof MappedObject) {
			throw InvalidState::create()
				->withMessage('Instance should be initialized by that moment');
		}

		if (!$context->shouldMapDataToObjects() && $data['structure'] instanceof MappedObject) {
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
		$array['afterArrayProcessingCallback'][] = $context->shouldMapDataToObjects();

		return $array;
	}

	/**
	 * @param array<mixed> $array
	 * @return array<mixed>
	 */
	public static function afterArrayInitialization(array $array, FieldContext $context): array
	{
		$array['afterArrayInitializationCallback'][] = $context->shouldMapDataToObjects();

		return $array;
	}

	/**
	 * @param array<mixed> $structure
	 * @return MappedObject|array<mixed>
	 * @throws InvalidData
	 */
	public static function afterStructure(array $structure, FieldContext $context)
	{
		$processor = $context->getProcessor();
		$options = $context->getOptions();
		$class = $options->getDynamicContext(CallbacksVoContext::class)->getDynamicStructureType();

		return $context->shouldMapDataToObjects()
			? $processor->process($structure, $class, $options)
			: $processor->processWithoutMapping($structure, $class, $options);
	}

	public static function beforeImmutableDefaultValue(): void
	{
		throw InvalidState::create()
			->withMessage(
				'Dont set "immutableDefaultValue". I am here to test before callback is not called when default value is used.',
			);
	}

	public static function afterImmutableDefaultValue(): void
	{
		throw InvalidState::create()
			->withMessage(
				'Dont set "immutableDefaultValue". I am here to test after callback is not called when default value is used.',
			);
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
