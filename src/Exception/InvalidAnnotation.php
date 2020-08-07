<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Exception;

use Orisai\Exceptions\LogicalException;

/**
 * Thrown for annotations with invalid parameters
 */
final class InvalidAnnotation extends LogicalException
{

	public static function create(): self
	{
		return new self();
	}

}
