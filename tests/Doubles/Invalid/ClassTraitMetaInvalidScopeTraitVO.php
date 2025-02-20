<?php declare(strict_types = 1);

namespace Tests\Orisai\ObjectMapper\Doubles\Invalid;

use Orisai\ObjectMapper\Callbacks\BeforeValidation;

/**
 * @BeforeValidation("before")
 */
trait ClassTraitMetaInvalidScopeTraitVO
{

	public function before(): void
	{
		// Noop
	}

}
