<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Processing;

use Orisai\ObjectMapper\MappedObject;
use ReflectionClass;

final class ObjectCreator
{

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
		$instance = (new ReflectionClass($class))->newInstanceWithoutConstructor();

		foreach ($injectors as $injector) {
			$this->injectorManager->get($injector)->inject($instance);
		}

		return $instance;
	}

}
