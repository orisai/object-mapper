<?php declare(strict_types = 1);

namespace Tests\Orisai\ObjectMapper\Doubles\PhpVersionSpecific;

use Orisai\ObjectMapper\MappedObject;
use Orisai\ObjectMapper\Modifiers\DefaultValue;
use Orisai\ObjectMapper\Rules\StringValue;

final class ReadonlyPropertiesVO implements MappedObject
{

	#[StringValue]
	public readonly string $readonly;

	#[DefaultValue('default')]
	#[StringValue]
	public readonly string $default1;

	#[DefaultValue('default')]
	#[StringValue]
	public readonly string $default2;

}
