<?php declare(strict_types = 1);

namespace Tests\Orisai\ObjectMapper\Doubles\Invalid;

use Orisai\ObjectMapper\MappedObject;
use Tests\Orisai\ObjectMapper\Doubles\Definition\TargetLessRuleDefinition;

final class DefinitionAboveMethodVO implements MappedObject
{

	#[TargetLessRuleDefinition]
	public function test(): void
	{
		// Noop
	}

}
