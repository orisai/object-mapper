<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Processing;

use Orisai\ObjectMapper\MappedObject;

/**
 * @template-covariant T of MappedObject
 */
interface DependencyInjector
{

	/**
	 * @return class-string<T>
	 */
	public function getClass(): string;

	/**
	 * @param T $object
	 */
	public function inject(MappedObject $object): void;

}
