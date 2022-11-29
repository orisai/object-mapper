<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Processing;

use Orisai\ObjectMapper\MappedObject;
use Orisai\ObjectMapper\Meta\Runtime\ClassRuntimeMeta;
use Orisai\ObjectMapper\Modifiers\CreateWithoutConstructorModifier;

/**
 * @template T of MappedObject
 */
final class ObjectHolder
{

	private ObjectCreator $creator;

	private ClassRuntimeMeta $meta;

	/** @var T|null */
	private ?MappedObject $instance;

	/** @var class-string<T> */
	private string $class;

	/**
	 * @param class-string<T> $class
	 * @param T|null $instance
	 */
	public function __construct(
		ObjectCreator $creator,
		ClassRuntimeMeta $meta,
		string $class,
		?MappedObject $instance = null
	)
	{
		$this->creator = $creator;
		$this->meta = $meta;
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
		return $this->instance ?? ($this->instance = $this->creator->createInstance(
			$this->class,
			$this->meta->getModifier(CreateWithoutConstructorModifier::class) === null,
		));
	}

}
