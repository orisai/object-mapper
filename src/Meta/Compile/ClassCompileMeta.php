<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Meta\Compile;

use ReflectionClass;

final class ClassCompileMeta extends NodeCompileMeta
{

	/** @var ReflectionClass<object> */
	private ReflectionClass $class;

	/**
	 * @param ReflectionClass<object> $class
	 */
	public function __construct(array $callbacks, array $docs, array $modifiers, ReflectionClass $class)
	{
		parent::__construct($callbacks, $docs, $modifiers);
		$this->class = $class;
	}

	public function hasAnyAttributes(): bool
	{
		return $this->getCallbacks() !== []
			|| $this->getDocs() !== []
			|| $this->getModifiers() !== [];
	}

	public function getClass(): ReflectionClass
	{
		return $this->class;
	}

}
