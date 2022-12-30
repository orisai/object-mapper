<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Processing;

use Orisai\ObjectMapper\MappedObject;

interface ObjectCreator
{

	/**
	 * @template T of MappedObject
	 * @param class-string<T> $class
	 * @return T
	 */
	public function createInstance(string $class, bool $useConstructor): MappedObject;

	/**
	 * @param class-string<MappedObject> $class
	 */
	public function checkClassIsInstantiable(string $class, bool $useConstructor): void;

}
