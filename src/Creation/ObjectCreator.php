<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Creation;

use Orisai\ObjectMapper\ValueObject;

interface ObjectCreator
{

	/**
	 * @template T of ValueObject
	 * @param class-string<T> $class
	 * @return T
	 */
	public function createInstance(string $class): ValueObject;

}
