<?php declare(strict_types = 1);

namespace Tests\Orisai\ObjectMapper\Doubles;

use Orisai\Exceptions\Logic\InvalidState;
use Orisai\ObjectMapper\Annotation\Callback\After;
use Orisai\ObjectMapper\Annotation\Callback\Before;
use Orisai\ObjectMapper\Annotation\Expect\StringValue;
use Orisai\ObjectMapper\Callbacks\CallbackRuntime;
use Orisai\ObjectMapper\Exception\ValueDoesNotMatch;
use Orisai\ObjectMapper\MappedObject;

/**
 * @Before(method="beforeClass", runtime=CallbackRuntime::ALWAYS)
 * @After(method="afterClass", runtime=CallbackRuntime::ALWAYS)
 */
final class PropertyCallbacksFailureVO extends MappedObject
{

	/**
	 * @StringValue()
	 * @Before(method="beforeNeverValidated", runtime=CallbackRuntime::ALWAYS)
	 */
	public string $neverValidated;

	/**
	 * @StringValue()
	 * @After(method="afterValidationFailed", runtime=CallbackRuntime::ALWAYS)
	 */
	public string $validationFailed;

	/**
	 * @param array<mixed> $data
	 * @return array<mixed>
	 */
	public static function beforeClass(array $data): array
	{
		$data['neverValidated'] = 123;
		$data['validationFailed'] = 123;

		return $data;
	}

	/**
	 * @param mixed $neverValidated
	 * @throws ValueDoesNotMatch
	 */
	public static function beforeNeverValidated($neverValidated): void
	{
		throw ValueDoesNotMatch::createFromString('Check before validation failed, field was never validated');
	}

	/**
	 * @param mixed $validationFailed
	 */
	public static function afterValidationFailed($validationFailed): void
	{
		throw InvalidState::create()
			->withMessage('I should be never called');
	}

	/**
	 * @param array<mixed> $data
	 */
	public static function afterClass(array $data): void
	{
		throw InvalidState::create()
			->withMessage('I should be never called');
	}

}
