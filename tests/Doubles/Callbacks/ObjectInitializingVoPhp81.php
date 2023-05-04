<?php declare(strict_types = 1);

namespace Tests\Orisai\ObjectMapper\Doubles\Callbacks;

use Orisai\ObjectMapper\MappedObject;
use Orisai\ObjectMapper\Modifiers\DefaultValue;
use Orisai\ObjectMapper\Rules\MappedObjectValue;
use Tests\Orisai\ObjectMapper\Doubles\DefaultsVO;

final class ObjectInitializingVoPhp81 implements MappedObject
{

	#[DefaultValue(new DefaultsVO())]
	#[MappedObjectValue(DefaultsVO::class)]
	public DefaultsVO $inner;

}
