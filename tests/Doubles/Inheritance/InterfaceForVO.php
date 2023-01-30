<?php declare(strict_types = 1);

namespace Tests\Orisai\ObjectMapper\Doubles\Inheritance;

use Orisai\ObjectMapper\Attributes\Callbacks\After;
use Orisai\ObjectMapper\MappedObject;

/**
 * @After("after")
 */
interface InterfaceForVO extends MappedObject
{

	/**
	 * @param array<mixed> $data
	 * @return array<mixed>
	 */
	public function after(array $data): array;

}
