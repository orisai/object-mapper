<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Bridge\NetteDI;

use Nette\DI\Container;
use Orisai\ObjectMapper\Creation\ObjectCreator;
use Orisai\ObjectMapper\ValueObject;
use function assert;

final class LazyObjectCreator implements ObjectCreator
{

	private Container $container;

	public function __construct(Container $container)
	{
		$this->container = $container;
	}

	public function createInstance(string $class): ValueObject
	{
		$object = $this->container->createInstance($class);
		assert($object instanceof $class);

		return $object;
	}

}
