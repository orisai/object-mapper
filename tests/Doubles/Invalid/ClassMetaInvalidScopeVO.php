<?php declare(strict_types = 1);

namespace Tests\Orisai\ObjectMapper\Doubles\Invalid;

use Orisai\ObjectMapper\Callbacks\BeforeValidation;

/**
 * @BeforeValidation("before")
 */
abstract class ClassMetaInvalidScopeVO
{

	public function before(): void
	{
		// Noop
	}

}
