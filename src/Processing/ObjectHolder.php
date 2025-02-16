<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Processing;

use Orisai\ObjectMapper\MappedObject;
use Orisai\ObjectMapper\Meta\Runtime\ClassRuntimeMeta;
use Orisai\ObjectMapper\Modifiers\RequiresDependenciesModifier;

/**
 * @template T of MappedObject
 */
final class ObjectHolder
{

	private ObjectCreator $creator;

	/** @var class-string<T> */
	private string $class;

	private ClassRuntimeMeta $meta;

	/** @var T|null */
	private ?MappedObject $instance = null;

	/**
	 * @param class-string<T> $class
	 */
	public function __construct(
		ObjectCreator $creator,
		string $class,
		ClassRuntimeMeta $meta
	)
	{
		$this->creator = $creator;
		$this->class = $class;
		$this->meta = $meta;
	}

	/**
	 * @return class-string<T>
	 */
	public function getClass(): string
	{
		return $this->class;
	}

	/**
	 * @return T
	 */
	public function getInstance(): MappedObject
	{
		if ($this->instance !== null) {
			return $this->instance;
		}

		$injectors = [];
		foreach ($this->meta->getModifier(RequiresDependenciesModifier::class) as $modifier) {
			$injectors[] = $modifier->args->injector;
		}

		return $this->instance = $this->creator->createInstance($this->class, $injectors);
	}

}
