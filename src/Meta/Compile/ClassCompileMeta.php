<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Meta\Compile;

use Orisai\ReflectionMeta\Structure\ClassStructure;

final class ClassCompileMeta extends NodeCompileMeta
{

	private ClassStructure $class;

	public function __construct(array $definitions, ClassStructure $class)
	{
		parent::__construct($definitions);
		$this->class = $class;
	}

	public function getClass(): ClassStructure
	{
		return $this->class;
	}

}
