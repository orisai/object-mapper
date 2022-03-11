<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Creation;

use Orisai\ObjectMapper\MappedObject;

interface ObjectCreator
{

	/**
	 * @template T of MappedObject
	 * @param class-string<T> $class
	 * @return T
	 */
	public function createInstance(string $class): MappedObject;

}
