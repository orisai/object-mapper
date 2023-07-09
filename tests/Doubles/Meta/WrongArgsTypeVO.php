<?php declare(strict_types = 1);

namespace Tests\Orisai\ObjectMapper\Doubles\Meta;

use Orisai\ObjectMapper\MappedObject;

final class WrongArgsTypeVO implements MappedObject
{

	/** @WrongArgsTypeValue() */
	public string $field;

}
