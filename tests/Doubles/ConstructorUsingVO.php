<?php declare(strict_types = 1);

namespace Tests\Orisai\ObjectMapper\Doubles;

use Orisai\ObjectMapper\Attributes\Expect\StringValue;
use Orisai\ObjectMapper\Attributes\Modifiers\CreateWithoutConstructor;
use Orisai\ObjectMapper\MappedObject;

/**
 * @CreateWithoutConstructor()
 */
final class ConstructorUsingVO extends MappedObject
{

	/** @StringValue() */
	public string $string;

	public function __construct(string $string)
	{
		$this->string = $string;
	}

}
