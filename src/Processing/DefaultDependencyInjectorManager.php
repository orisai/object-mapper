<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Processing;

use Orisai\Exceptions\Logic\InvalidState;
use Orisai\ObjectMapper\MappedObject;
use function get_class;

final class DefaultDependencyInjectorManager implements DependencyInjectorManager
{

	/** @var array<class-string<DependencyInjector<MappedObject>>, DependencyInjector<MappedObject>> */
	private array $injectors = [];

	/**
	 * @param DependencyInjector<MappedObject> $injector
	 */
	public function addInjector(DependencyInjector $injector): void
	{
		$this->injectors[get_class($injector)] = $injector;
	}

	public function get(string $injector): DependencyInjector
	{
		$injectorInst = $this->injectors[$injector] ?? null;

		if ($injectorInst === null) {
			throw InvalidState::create()
				->withMessage("Injector '$injector' does not exist.");
		}

		return $injectorInst;
	}

}
