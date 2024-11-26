<?php declare(strict_types = 1);

namespace Tests\Orisai\ObjectMapper\Doubles\Invalid;

use Orisai\ObjectMapper\Callbacks\Before;

/**
 * @Before("before")
 */
interface ClassInterfaceMetaInvalidScopeInterfaceVO
{

	public function before(): void;

}
