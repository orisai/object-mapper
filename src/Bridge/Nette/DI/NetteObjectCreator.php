<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Bridge\Nette\DI;

use Nette\DI\Container;
use Orisai\ObjectMapper\Creation\ObjectCreator;
use Orisai\ObjectMapper\ValueObject;
use function assert;

final class NetteObjectCreator implements ObjectCreator
{

	private Container $container;

	public function __construct(Container $container)
	{
		$this->container = $container;
	}

	/**
	 * @template T of ValueObject
	 * @phpstan-param class-string<T> $class
	 * @phpstan-return T
	 */
	public function createInstance(string $class): ValueObject
	{
		$object = $this->container->createInstance($class);
		assert($object instanceof ValueObject);

		return $object;
	}

}
