<?php declare(strict_types = 1);

namespace Tests\Orisai\ObjectMapper\Doubles\PhpVersionSpecific;

use Orisai\ObjectMapper\MappedObject;
use Orisai\ObjectMapper\Modifiers\DefaultValue;
use Orisai\ObjectMapper\Rules\InstanceOfValue;
use stdClass;

final class ObjectDefaultVO implements MappedObject
{

	#[DefaultValue(new stdClass())]
	#[InstanceOfValue(stdClass::class)]
	public stdClass $class;

}
