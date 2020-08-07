<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Exception;

use Orisai\Exceptions\LogicalException;

/**
 * Thrown when a meta reader finds MetadataLoader returned invalid meta
 */
final class InvalidMeta extends LogicalException
{

	public static function create(): self
	{
		return new self();
	}

}
