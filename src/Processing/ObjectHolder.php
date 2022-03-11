<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Processing;

use Orisai\ObjectMapper\Creation\ObjectCreator;
use Orisai\ObjectMapper\MappedObject;

/**
 * @template T of MappedObject
 */
final class ObjectHolder
{

	private ObjectCreator $creator;

	/** @var T|null */
	private ?MappedObject $instance;

	/** @var class-string<T> */
	private string $class;

	/**
	 * @param class-string<T> $class
	 * @param T|null $instance
	 */
	public function __construct(ObjectCreator $creator, string $class, ?MappedObject $instance = null)
	{
		$this->creator = $creator;
		$this->class = $class;
		$this->instance = $instance;
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
		return $this->instance ?? ($this->instance = $this->creator->createInstance($this->class));
	}

}
