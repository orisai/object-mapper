<?php declare(strict_types = 1);

namespace Tests\Orisai\ObjectMapper\Doubles\Invalid;

use Orisai\ObjectMapper\MappedObject;
use Orisai\ObjectMapper\Rules\StringValue;
use Tests\Orisai\ObjectMapper\Doubles\Callbacks\WrongArgsTypeCallbackValue;

final class WrongCallbackArgsTypeVO implements MappedObject
{

	/**
	 * @WrongArgsTypeCallbackValue()
	 * @StringValue()
	 */
	public string $field;

}
