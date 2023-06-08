<?php declare(strict_types = 1);

namespace Tests\Orisai\ObjectMapper\Doubles\Dependencies;

use Orisai\ObjectMapper\MappedObject;
use Orisai\ObjectMapper\Modifiers\RequiresDependencies;
use Orisai\ObjectMapper\Rules\StringValue;
use stdClass;

/**
 * @RequiresDependencies(injector=DependenciesUsingVoInjector::class)
 */
final class DependenciesUsingVo implements MappedObject
{

	/** @StringValue() */
	public string $field;

	public stdClass $dependency;

	public function __construct(string $field, stdClass $dependency)
	{
		$this->field = $field;
		$this->dependency = $dependency;
	}

}
