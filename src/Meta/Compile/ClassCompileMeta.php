<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Meta\Compile;

use Orisai\ReflectionMeta\Structure\ClassStructure;

final class ClassCompileMeta extends NodeCompileMeta
{

	private ClassStructure $class;

	public function __construct(array $callbacks, array $docs, array $modifiers, ClassStructure $class)
	{
		parent::__construct($callbacks, $docs, $modifiers);
		$this->class = $class;
	}

	public function hasAnyMeta(): bool
	{
		return $this->getCallbacks() !== []
			|| $this->getDocs() !== []
			|| $this->getModifiers() !== [];
	}

	public function getClass(): ClassStructure
	{
		return $this->class;
	}

}
