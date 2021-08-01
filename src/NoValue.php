<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper;

final class NoValue
{

	/**
	 * Represents a completely missing value
	 */
	private function __construct()
	{

	}

	public static function create(): self
	{
		return new self();
	}

}
