<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Processing;

use Orisai\ObjectMapper\Creation\ObjectCreator;
use Orisai\ObjectMapper\ValueObject;

/**
 * @template T of ValueObject
 */
final class ObjectHolder
{

	private ObjectCreator $creator;

	/** @var T|null */
	private ?ValueObject $instance;

	/** @var class-string<T> */
	private string $class;

	/**
	 * @param class-string<T> $class
	 * @param T|null $instance
	 */
	public function __construct(ObjectCreator $creator, string $class, ?ValueObject $instance = null)
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
	public function getInstance(): ValueObject
	{
		return $this->instance ?? ($this->instance = $this->creator->createInstance($this->class));
	}

}
