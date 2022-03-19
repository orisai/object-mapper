<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Args;

use Orisai\ObjectMapper\Callbacks\Callback;
use Orisai\ObjectMapper\Meta\CallbackMeta;
use Orisai\ObjectMapper\Meta\RuleMeta;
use Orisai\ObjectMapper\Rules\Rule;

trait ArgsCreator
{

	/**
	 * @param Rule<Args> $rule
	 */
	protected function createRuleArgsInst(Rule $rule, RuleMeta $meta): Args
	{
		return $rule->getArgsType()::fromArray($meta->getArgs());
	}

	/**
	 * @param class-string<Callback<Args>> $callbackType
	 */
	protected function createCallbackArgsInst(string $callbackType, CallbackMeta $meta): Args
	{
		return $callbackType::getArgsType()::fromArray($meta->getArgs());
	}

}
