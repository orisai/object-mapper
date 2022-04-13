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
	public string $type;

	/**
	 * @param class-string<MappedObject> $type
	 */
	public function __construct(string $type)
	{
		$this->type = $type;
	}

}
