<?php declare(strict_types = 1);

namespace Tests\Orisai\ObjectMapper\Doubles\Callbacks;

use Orisai\ObjectMapper\Attributes\Expect\MappedObjectValue;
use Orisai\ObjectMapper\Attributes\Modifiers\DefaultValue;
use Orisai\ObjectMapper\MappedObject;
use Tests\Orisai\ObjectMapper\Doubles\DefaultsVO;

final class ObjectInitializingVoPhp81 implements MappedObject
{

	#[DefaultValue(new DefaultsVO())]
	#[MappedObjectValue(DefaultsVO::class)]
	public DefaultsVO $inner;

}
