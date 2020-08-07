<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Creation;

use Orisai\ObjectMapper\ValueObject;

interface ObjectCreator
{

	/**
	 * @template T of ValueObject
	 * @phpstan-param class-string<T> $class
	 * @phpstan-return T
	 */
	public function createInstance(string $class): ValueObject;

}
