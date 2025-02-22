<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Processing;

use Orisai\ObjectMapper\MappedObject;
use ReflectionClass;

final class ObjectCreator
{

	/** @var array<class-string<MappedObject>, ReflectionClass<MappedObject>> */
	private array $reflectors = [];

	private DependencyInjectorManager $injectorManager;

	public function __construct(DependencyInjectorManager $injectorManager)
	{
		$this->injectorManager = $injectorManager;
	}

	/**
	 * @template T of MappedObject
	 * @param class-string<T>                           $class
	 * @param list<class-string<DependencyInjector<T>>> $injectors
	 * @return T
	 */
	public function createInstance(string $class, array $injectors): MappedObject
	{
		$reflector = $this->reflectors[$class]
			?? ($this->reflectors[$class] = new ReflectionClass($class));

		$instance = $reflector->newInstanceWithoutConstructor();

		foreach ($injectors as $injector) {
			$this->injectorManager->get($injector)->inject($instance);
		}

		return $instance;
	}

	public function reset(): void
	{
		$this->reflectors = [];
	}

}
