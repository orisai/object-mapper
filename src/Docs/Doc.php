<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Docs;

use Orisai\ObjectMapper\Context\ArgsContext;

interface Doc
{

	/**
	 * @param array<mixed> $args
	 * @return array<mixed>
	 */
	public static function processArgs(array $args, ArgsContext $context): array;

	public static function getUniqueName(): string;

}
