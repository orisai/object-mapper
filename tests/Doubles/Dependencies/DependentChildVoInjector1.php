<?php declare(strict_types = 1);

namespace Tests\Orisai\ObjectMapper\Doubles\Dependencies;

use Orisai\ObjectMapper\MappedObject;
use Orisai\ObjectMapper\Processing\DependencyInjector;

/**
 * @implements DependencyInjector<DependentChildVO>
 */
final class DependentChildVoInjector1 implements DependencyInjector
{

	private string $dependency;

	public function __construct(string $dependency)
	{
		$this->dependency = $dependency;
	}

	public function getClass(): string
	{
		return DependentChildVO::class;
	}

	/**
	 * @param DependentChildVO $object
	 */
	public function inject(MappedObject $object): void
	{
		$object->child1 = $this->dependency;
	}

}
