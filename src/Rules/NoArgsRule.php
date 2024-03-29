<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Rules;

use Orisai\ObjectMapper\Args\ArgsChecker;
use Orisai\ObjectMapper\Args\EmptyArgs;
use Orisai\ObjectMapper\Context\ArgsContext;

trait NoArgsRule
{

	public function resolveArgs(array $args, ArgsContext $context): EmptyArgs
	{
		$checker = new ArgsChecker($args, static::class);
		$checker->checkNoArgs();

		return new EmptyArgs();
	}

	public function getArgsType(): string
	{
		return EmptyArgs::class;
	}

}
