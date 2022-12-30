<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Bridge\NetteDI;

use Nette\DI\Container;
use Orisai\ObjectMapper\MappedObject;
use Orisai\ObjectMapper\Processing\ObjectCreator;
use ReflectionClass;
use function assert;

final class LazyObjectCreator implements ObjectCreator
{

	private Container $container;

	public function __construct(Container $container)
	{
		$this->container = $container;
	}

	public function createInstance(string $class, bool $useConstructor): MappedObject
	{
		if (!$useConstructor) {
			return (new ReflectionClass($class))->newInstanceWithoutConstructor();
		}

		$object = $this->container->createInstance($class);
		assert($object instanceof $class);

		return $object;
	}

	public function checkClassIsInstantiable(string $class, bool $useConstructor): void
	{
		$this->createInstance($class, $useConstructor);
	}

}
