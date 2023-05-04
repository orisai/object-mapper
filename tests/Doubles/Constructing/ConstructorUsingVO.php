<?php declare(strict_types = 1);

namespace Tests\Orisai\ObjectMapper\Doubles\Constructing;

use Orisai\ObjectMapper\Attributes\Modifiers\CreateWithoutConstructor;
use Orisai\ObjectMapper\MappedObject;
use Orisai\ObjectMapper\Rules\StringValue;

/**
 * @CreateWithoutConstructor()
 */
final class ConstructorUsingVO implements MappedObject
{

	/** @StringValue() */
	public string $string;

	public function __construct(string $string)
	{
		$this->string = $string;
	}

}
