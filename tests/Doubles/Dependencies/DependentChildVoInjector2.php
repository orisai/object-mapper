<?php declare(strict_types = 1);

namespace Tests\Orisai\ObjectMapper\Doubles\Dependencies;

use Orisai\ObjectMapper\MappedObject;
use Orisai\ObjectMapper\Processing\DependencyInjector;

/**
 * @implements DependencyInjector<DependentChildVO>
 */
final class DependentChildVoInjector2 implements DependencyInjector
{

	private int $dependency;

	public function __construct(int $dependency)
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
		$object->child2 = $this->dependency;
	}

}
