<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Meta;

use Orisai\ObjectMapper\Callbacks\Callback;
use Orisai\ObjectMapper\Rules\Rule;

trait ArgsCreator
{

	protected function createRuleArgsInst(Rule $rule, RuleMeta $meta): Args
	{
		return $rule->getArgsType()::fromArray($meta->getArgs());
	}

	/**
	 * @param class-string<Callback> $callbackType
	 */
	protected function createCallbackArgsInst(string $callbackType, CallbackMeta $meta): Args
	{
		return $callbackType::getArgsType()::fromArray($meta->getArgs());
	}

}
