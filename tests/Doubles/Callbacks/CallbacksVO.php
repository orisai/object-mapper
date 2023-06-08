<?php declare(strict_types = 1);

namespace Tests\Orisai\ObjectMapper\Doubles\Callbacks;

use Orisai\Exceptions\Logic\InvalidState;
use Orisai\ObjectMapper\Callbacks\After;
use Orisai\ObjectMapper\Callbacks\Before;
use Orisai\ObjectMapper\Callbacks\CallbackRuntime;
use Orisai\ObjectMapper\Context\FieldContext;
use Orisai\ObjectMapper\Context\MappedObjectContext;
use Orisai\ObjectMapper\Exception\InvalidData;
use Orisai\ObjectMapper\MappedObject;
use Orisai\ObjectMapper\Rules\ArrayOf;
use Orisai\ObjectMapper\Rules\MixedValue;
use Orisai\ObjectMapper\Rules\StringValue;
use function array_key_exists;
use function is_array;

/**
 * @Before(method="beforeClass", runtime=CallbackRuntime::Always)
 * @After(method="afterClass", runtime=CallbackRuntime::Always)
 */
final class CallbacksVO implements MappedObject
{

	/**
	 * @var array<string, array<mixed>>
	 *
	 * @ArrayOf(
	 *     key=@StringValue(),
	 *     item=@ArrayOf(
	 *         @MixedValue(),
	 *     ),
	 * )
	 * @After(method="afterArrayInitialization", runtime=CallbackRuntime::Process)
	 * @After(method="afterArrayProcessing", runtime=CallbackRuntime::ProcessWithoutMapping)
	 */
	public array $array;

	/**
	 * @ArrayOf(
	 *     @MixedValue()
	 * )
	 * @After(method="afterStructure", runtime=CallbackRuntime::Always)
	 */
	public MappedObject $structure;

	/** @StringValue() */
	public string $overriddenDefaultValue = 'defaultValue_before_override';

	/**
	 * @StringValue()
	 * @Before(method="beforeImmutableDefaultValue", runtime=CallbackRuntime::Always)
	 * @Before(method="afterImmutableDefaultValue", runtime=CallbackRuntime::Always)
	 */
	public string $immutableDefaultValue = 'defaultValue_immutable';

	/** @StringValue() */
	public string $requiredValue;

	/**
	 * @StringValue()
	 * @Before(method="beforeCallbackSetValue", runtime=CallbackRuntime::Always)
	 * @After(method="afterCallbackSetValue", runtime=CallbackRuntime::Always)
	 */
	public string $callbackSetValue;

	/**
	 * @param mixed $data
	 * @return mixed
	 */
	public static function beforeClass($data, MappedObjectContext $context)
	{
		if (!is_array($data)) {
			return $data;
		}

		$data['array']['beforeClassCallback'][] = $context->shouldInitializeObjects();

		// Set default value, processor don't know it's going to be structure and thinks value is required
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
	public static function afterClass(array $data, MappedObjectContext $context): array
	{
		$data['array']['afterClassCallback'][] = $context->shouldInitializeObjects();

		if ($context->shouldInitializeObjects() && !$data['structure'] instanceof MappedObject) {
			throw InvalidState::create()
				->withMessage('Instance should be initialized by that moment');
		}

		if (!$context->shouldInitializeObjects() && $data['structure'] instanceof MappedObject) {
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
		$array['afterArrayProcessingCallback'][] = $context->shouldInitializeObjects();

		return $array;
	}

	/**
	 * @param array<mixed> $array
	 * @return array<mixed>
	 */
	public static function afterArrayInitialization(array $array, FieldContext $context): array
	{
		$array['afterArrayInitializationCallback'][] = $context->shouldInitializeObjects();

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
		$class = $options->getDynamicContext(CallbacksVoContext::class)->getObjectType();

		return $context->shouldInitializeObjects()
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
		return 'givenByBeforeCallback';
	}

	public function afterCallbackSetValue(string $value): void
	{
		// Does not return, callback return value should not override value
	}

}
