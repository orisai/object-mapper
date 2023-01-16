<?php declare(strict_types = 1);

namespace Tests\Orisai\ObjectMapper\Doubles\Callbacks;

use Orisai\Exceptions\Logic\InvalidState;
use Orisai\ObjectMapper\Attributes\Callbacks\After;
use Orisai\ObjectMapper\Attributes\Callbacks\Before;
use Orisai\ObjectMapper\Attributes\Expect\StringValue;
use Orisai\ObjectMapper\Exception\ValueDoesNotMatch;
use Orisai\ObjectMapper\MappedObject;
use Orisai\ObjectMapper\Types\Value;
use function is_array;

/**
 * @Before(method="beforeClass")
 * @After(method="afterClass")
 */
final class PropertyCallbacksFailureVO implements MappedObject
{

	/**
	 * @StringValue()
	 * @Before(method="beforeNeverValidated")
	 */
	public string $neverValidated;

	/**
	 * @StringValue()
	 * @After(method="afterValidationFailed")
	 */
	public string $validationFailed;

	/**
	 * @param mixed $data
	 * @return mixed
	 */
	public static function beforeClass($data)
	{
		if (!is_array($data)) {
			return $data;
		}

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
		throw ValueDoesNotMatch::createFromString(
			'Check before validation failed, field was never validated',
			Value::none(),
		);
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
