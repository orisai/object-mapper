<?php declare(strict_types = 1);

namespace Tests\Orisai\ObjectMapper\Doubles;

use Orisai\ObjectMapper\MappedObject;
use Orisai\ObjectMapper\Rules\StringValue;

final class InternalClassExtendingVO extends \stdClass implements MappedObject
{

	/** @StringValue() */
	public string $field;

	public function __construct(string $field)
	{
		$this->field = $field;
	}

}
