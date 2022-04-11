<?php declare(strict_types = 1);

namespace Tests\Orisai\ObjectMapper\Doubles;

use Orisai\ObjectMapper\Attributes\Callbacks\Before;
use Orisai\ObjectMapper\MappedObject;

/**
 * @Before(method="before")
 */
final class BeforeClassCallbackMixedValueVO extends MappedObject
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
