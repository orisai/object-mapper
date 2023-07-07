<?php declare(strict_types = 1);

namespace Tests\Orisai\ObjectMapper\Doubles;

use Orisai\ObjectMapper\MappedObject;
use Orisai\ObjectMapper\Rules\StringValue;
use RuntimeException;

final class ForbiddenConstructorVO implements MappedObject
{

	/** @StringValue() */
	public string $field;

	public function __construct()
	{
		throw new RuntimeException('constructor is forbidden');
	}

}
