<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Rules;

use Orisai\ObjectMapper\Args\ArgsChecker;
use Orisai\ObjectMapper\Context\RuleArgsContext;

trait NoArgsRule
{

	/**
	 * {@inheritDoc}
	 */
	public function resolveArgs(array $args, RuleArgsContext $context): EmptyArgs
	{
		$checker = new ArgsChecker($args, static::class);
		$checker->checkNoArgs();

		return EmptyArgs::fromArray($args);
	}

	public function getArgsType(): string
	{
		return EmptyArgs::class;
	}

}
