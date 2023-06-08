<?php declare(strict_types = 1);

namespace Tests\Orisai\ObjectMapper\Doubles\Dependencies;

use Orisai\ObjectMapper\MappedObject;
use Orisai\ObjectMapper\Processing\DependencyInjector;
use stdClass;

/**
 * @implements DependencyInjector<DependentBaseVO>
 */
final class DependentBaseVoInjector implements DependencyInjector
{

	private stdClass $dependency;

	public function __construct(stdClass $dependency)
	{
		$this->dependency = $dependency;
	}

	public function getClass(): string
	{
		return DependentBaseVO::class;
	}

	/**
	 * @param DependentBaseVO $object
	 */
	public function inject(MappedObject $object): void
	{
		$object->base1 = $this->dependency;
	}

}
