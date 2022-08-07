<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Docs;

use Orisai\ObjectMapper\Context\ResolverArgsContext;

interface Doc
{

	/**
	 * @param array<mixed> $args
	 * @return array<mixed>
	 */
	public static function resolveArgs(array $args, ResolverArgsContext $context): array;

	public static function getUniqueName(): string;

}
