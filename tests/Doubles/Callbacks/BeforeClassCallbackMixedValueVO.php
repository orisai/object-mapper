<?php declare(strict_types = 1);

namespace Tests\Orisai\ObjectMapper\Doubles\Callbacks;

use Orisai\ObjectMapper\Attributes\Callbacks\Before;
use Orisai\ObjectMapper\MappedObject;

/**
 * @Before(method="before")
 */
final class BeforeClassCallbackMixedValueVO implements MappedObject
{

	/**
	 * @param mixed $data
	 * @return mixed
	 */
	public static function before($data)
	{
		if ($data === true) {
			return [];
		}

		return $data;
	}

}
