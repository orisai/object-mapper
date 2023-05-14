<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Rules;

use Orisai\ObjectMapper\Args\Args;
use Orisai\ObjectMapper\MappedObject;

/**
 * @internal
 */
final class MappedObjectArgs implements Args
{

	/** @var class-string<MappedObject> */
	public string $class;

	/**
	 * @param class-string<MappedObject> $class
	 */
	public function __construct(string $class)
	{
		$this->class = $class;
	}

}
