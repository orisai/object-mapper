<?php declare(strict_types = 1);

namespace Tests\Orisai\ObjectMapper\Doubles\Dependencies;

use Orisai\ObjectMapper\MappedObject;
use Orisai\ObjectMapper\Processing\DependencyInjector;
use stdClass;

/**
 * @implements DependencyInjector<DependenciesUsingVo>
 */
final class DependenciesUsingVoInjector implements DependencyInjector
{

	private stdClass $dependency;

	public function __construct(stdClass $dependency)
	{
		$this->dependency = $dependency;
	}

	public function getClass(): string
	{
		return DependenciesUsingVo::class;
	}

	/**
	 * @param DependenciesUsingVo $object
	 */
	public function inject(MappedObject $object): void
	{
		$object->dependency = $this->dependency;
	}

}
