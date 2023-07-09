<?php declare(strict_types = 1);

namespace Tests\Orisai\ObjectMapper\Doubles\Meta;

use Orisai\ObjectMapper\MappedObject;

final class ClassInterfaceMetaInvalidScopeRootVO implements MappedObject, ClassInterfaceMetaInvalidScopeInterfaceVO
{

	public function before(): void
	{
		// Noop
	}

}
