<?php declare(strict_types = 1);

namespace Tests\Orisai\ObjectMapper\Doubles\Constructing;

use Orisai\ObjectMapper\MappedObject;
use stdClass;

final class DependentVO implements MappedObject
{

	public ?stdClass $class = null;

	public function __construct(stdClass $class)
	{
		$this->class = $class;
	}

}
