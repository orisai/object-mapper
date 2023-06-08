<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Modifiers;

use Orisai\ObjectMapper\Args\Args;
use Orisai\ObjectMapper\MappedObject;
use Orisai\ObjectMapper\Processing\DependencyInjector;

/**
 * @template T of MappedObject
 *
 * @internal
 */
final class RequiresDependenciesArgs implements Args
{

	/** @var class-string<DependencyInjector<T>> */
	public string $injector;

	/**
	 * @param class-string<DependencyInjector<T>> $injector
	 */
	public function __construct(string $injector)
	{
		$this->injector = $injector;
	}

}
