<?php declare(strict_types = 1);

namespace Tests\Orisai\ObjectMapper\Fixtures;

use Orisai\ObjectMapper\ValueObject;
use stdClass;

final class DependentVO extends ValueObject
{

	public ?stdClass $class = null;

	public function __construct(stdClass $class)
	{
		$this->class = $class;
	}

}
