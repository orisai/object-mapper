<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Processing;

interface DependencyInjectorManager
{

	/**
	 * @template T of DependencyInjector
	 * @param class-string<T> $injector
	 * @return T
	 */
	public function get(string $injector): DependencyInjector;

}
