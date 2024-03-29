<?php declare(strict_types = 1);

namespace Tests\Orisai\ObjectMapper\Doubles\Inheritance\TraitCallback;

use Orisai\ObjectMapper\Callbacks\After;
use Orisai\ObjectMapper\Callbacks\Before;
use Orisai\ObjectMapper\MappedObject;
use Orisai\ObjectMapper\Rules\StringValue;
use function is_array;
use function is_string;

/**
 * @Before("before")
 * @After("after")
 */
trait A
{

	/** @StringValue() */
	public string $string;

	/**
	 * @param mixed $data
	 * @return mixed
	 */
	private function before($data)
	{
		if (!is_array($data)) {
			return $data;
		}

		if (is_string($data['string'] ?? null)) {
			$data['string'] = 'A::before-' . $data['string'];
		}

		return $data;
	}

	/**
	 * @param array<mixed> $data
	 * @return array<mixed>
	 */
	private function after(array $data): array
	{
		$data['string'] .= '-A::after';

		return $data;
	}

}

final class TraitCallbackVO implements MappedObject
{

	use A;

}
