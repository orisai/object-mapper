<?php declare(strict_types = 1);

namespace Tests\Orisai\ObjectMapper\Doubles\Invalid;

use Orisai\ObjectMapper\MappedObject;
use Tests\Orisai\ObjectMapper\Doubles\Rules\WrongArgsTypeRuleValue;

final class WrongRuleArgsTypeVO implements MappedObject
{

	/** @WrongArgsTypeRuleValue() */
	public string $field;

}
