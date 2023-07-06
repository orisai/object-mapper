<?php declare(strict_types = 1);

namespace Tests\Orisai\ObjectMapper\Doubles\PhpVersionSpecific;

use Orisai\ObjectMapper\MappedObject;
use Orisai\ObjectMapper\Rules\StringValue;

final class AttributesVO implements MappedObject
{

	#[StringValue]
	public string $string;

	public function __construct(string $string)
	{
		$this->string = $string;
	}

}
