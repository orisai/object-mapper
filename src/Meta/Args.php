<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Meta;

interface Args
{

	/**
	 * @param array<mixed> $args
	 */
	public static function fromArray(array $args): self;

}
