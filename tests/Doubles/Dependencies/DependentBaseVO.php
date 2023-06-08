<?php declare(strict_types = 1);

namespace Tests\Orisai\ObjectMapper\Doubles\Dependencies;

use Orisai\ObjectMapper\MappedObject;
use Orisai\ObjectMapper\Modifiers\RequiresDependencies;
use stdClass;

/**
 * @RequiresDependencies(injector=DependentBaseVoInjector::class)
 */
abstract class DependentBaseVO implements MappedObject
{

	public stdClass $base1;

	public function __construct(stdClass $base1)
	{
		$this->base1 = $base1;
	}

}
