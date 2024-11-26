<?php declare(strict_types = 1);

namespace Tests\Orisai\ObjectMapper\Doubles\Invalid;

use Orisai\ObjectMapper\MappedObject;
use Tests\Orisai\ObjectMapper\Doubles\Meta\WrongArgsTypeValue;

final class WrongArgsTypeVO implements MappedObject
{

	/** @WrongArgsTypeValue() */
	public string $field;

}
