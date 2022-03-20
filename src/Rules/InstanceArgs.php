<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Rules;

use Orisai\ObjectMapper\Args\Args;

/**
 * @internal
 */
final class InstanceArgs implements Args
{

	/** @var class-string */
	public string $type;

	/**
	 * @param class-string $type
	 */
	public function __construct(string $type)
	{
		$this->type = $type;
	}

}
