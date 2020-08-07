<?php declare(strict_types = 1);

namespace Tests\Orisai\ObjectMapper\Fixtures;

use Orisai\Exceptions\Logic\InvalidState;
use Orisai\ObjectMapper\Annotation\Callback;
use Orisai\ObjectMapper\Annotation\Expect;
use Orisai\ObjectMapper\Callbacks\CallbackRuntime;
use Orisai\ObjectMapper\Exception\ValueDoesNotMatch;
use Orisai\ObjectMapper\ValueObject;

/**
 * @Callback\Before(method="beforeClass", runtime=CallbackRuntime::ALWAYS)
 * @Callback\After(method="afterClass", runtime=CallbackRuntime::ALWAYS)
 */
final class PropertyCallbacksFailureVO extends ValueObject
{

	/**
	 * @Expect\StringValue()
	 * @Callback\Before(method="beforeNeverValidated", runtime=CallbackRuntime::ALWAYS)
	 */
	public string $neverValidated;

	/**
	 * @Expect\StringValue()
	 * @Callback\After(method="afterValidationFailed", runtime=CallbackRuntime::ALWAYS)
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
