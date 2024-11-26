<?php declare(strict_types = 1);

namespace Tests\Orisai\ObjectMapper\Doubles\Php81;

use Orisai\ObjectMapper\MappedObject;
use Orisai\ObjectMapper\Modifiers\DefaultValue;
use Orisai\ObjectMapper\Rules\MappedObjectValue;
use Tests\Orisai\ObjectMapper\Doubles\DefaultsVO;

final class ObjectInitializingInAttributeVo implements MappedObject
{

	#[DefaultValue(new DefaultsVO())]
	#[MappedObjectValue(DefaultsVO::class)]
	public DefaultsVO $inner;

	public function __construct(DefaultsVO $inner)
	{
		$this->inner = $inner;
	}

}
