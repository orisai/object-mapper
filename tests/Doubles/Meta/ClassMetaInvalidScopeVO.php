<?php declare(strict_types = 1);

namespace Tests\Orisai\ObjectMapper\Doubles\Meta;

use Orisai\ObjectMapper\Callbacks\Before;

/**
 * @Before("before")
 */
abstract class ClassMetaInvalidScopeVO
{

	public function before(): void
	{
		// Noop
	}

}
