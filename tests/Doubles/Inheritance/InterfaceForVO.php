<?php declare(strict_types = 1);

namespace Tests\Orisai\ObjectMapper\Doubles\Inheritance;

use Orisai\ObjectMapper\Callbacks\AfterValidation;
use Orisai\ObjectMapper\MappedObject;

/**
 * @AfterValidation("after")
 */
interface InterfaceForVO extends MappedObject
{

	/**
	 * @param array<mixed> $data
	 * @return array<mixed>
	 */
	public function after(array $data): array;

}
