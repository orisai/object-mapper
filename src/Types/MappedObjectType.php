<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Types;

use Orisai\ObjectMapper\MappedObject;

final class MappedObjectType extends ArrayShapeType
{

	/** @var class-string<MappedObject> */
	private string $class;

	/**
	 * @param class-string<MappedObject> $class
	 */
	public function __construct(string $class)
	{
		$this->class = $class;
	}

	/**
	 * @return class-string<MappedObject>
	 */
	public function getClass(): string
	{
		return $this->class;
	}

}
