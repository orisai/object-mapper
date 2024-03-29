<?php declare(strict_types = 1);

namespace Tests\Orisai\ObjectMapper\Doubles\PhpVersionSpecific;

use Orisai\ObjectMapper\MappedObject;
use Orisai\ObjectMapper\Modifiers\DefaultValue;
use Orisai\ObjectMapper\Rules\StringValue;

final readonly class ReadonlyClassVO implements MappedObject
{

	#[StringValue]
	public string $readonly;

	#[DefaultValue('default')]
	#[StringValue]
	public string $default1;

	#[DefaultValue('default')]
	#[StringValue]
	public string $default2;

	public function __construct(string $readonly, string $default1, string $default2)
	{
		$this->readonly = $readonly;
		$this->default1 = $default1;
		$this->default2 = $default2;
	}

}
