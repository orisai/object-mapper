<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Types;

final class NoValue
{

	private function __construct()
	{
		// Represents a completely missing value
	}

	public static function create(): self
	{
		return new self();
	}

}
