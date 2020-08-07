<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Rules;

use Orisai\ObjectMapper\Context\RuleArgsContext;
use Orisai\ObjectMapper\Meta\ArgsChecker;

trait NoArgsRule
{

	/**
	 * @param array<mixed> $args
	 * @return array<mixed>
	 */
	public function resolveArgs(array $args, RuleArgsContext $context): array
	{
		$checker = new ArgsChecker($args, static::class);
		$checker->checkNoArgs();

		return [];
	}

	public function getArgsType(): string
	{
		return EmptyArgs::class;
	}

}
