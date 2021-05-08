<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Processing;

use Orisai\ObjectMapper\Creation\ObjectCreator;
use Orisai\ObjectMapper\ValueObject;

final class ObjectHolder
{

	private ObjectCreator $creator;

	private ?ValueObject $instance;

	/** @var class-string<ValueObject> */
	private string $class;

	/**
	 * @param class-string<ValueObject> $class
	 */
	public function __construct(ObjectCreator $creator, string $class, ?ValueObject $instance = null)
	{
		$this->creator = $creator;
		$this->class = $class;
		$this->instance = $instance;
	}

	/**
	 * @return class-string<ValueObject>
	 */
	public function getClass(): string
	{
		return $this->class;
	}

	public function getInstance(): ValueObject
	{
		if ($this->instance !== null) {
			return $this->instance;
		}

		return $this->instance = $this->creator->createInstance($this->class);
	}

}
