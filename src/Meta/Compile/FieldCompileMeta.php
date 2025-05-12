<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Meta\Compile;

use Orisai\ReflectionMeta\Structure\PropertyStructure;

final class FieldCompileMeta extends NodeCompileMeta
{

	private PropertyStructure $property;

	public function __construct(array $definitions, PropertyStructure $property)
	{
		parent::__construct($definitions);
		$this->property = $property;
	}

	public function getPropertyStructure(): PropertyStructure
	{
		return $this->property;
	}

}
