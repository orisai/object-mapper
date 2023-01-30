<?php declare(strict_types = 1);

namespace Tests\Orisai\ObjectMapper\Doubles\PhpVersionSpecific;

use Orisai\ObjectMapper\Attributes\Expect\InstanceOfValue;
use Orisai\ObjectMapper\Attributes\Modifiers\DefaultValue;
use Orisai\ObjectMapper\MappedObject;
use stdClass;

final class ObjectDefaultVO implements MappedObject
{

	#[DefaultValue(new stdClass())]
	#[InstanceOfValue(stdClass::class)]
	public stdClass $class;

}
