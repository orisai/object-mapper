<?php declare(strict_types = 1);

namespace Tests\Orisai\ObjectMapper\Doubles\Invalid;

use Orisai\ObjectMapper\MappedObject;
use Tests\Orisai\ObjectMapper\Doubles\Definition\TargetLessRuleDefinition;

final class DefinitionAboveParameterVO implements MappedObject
{

	public function test(#[TargetLessRuleDefinition] string $test,): void
	{
		// Noop
	}

}
