<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Rules;

use Orisai\Exceptions\Logic\InvalidArgument;
use Orisai\ObjectMapper\Args\Args;
use Orisai\ObjectMapper\Args\ArgsChecker;
use Orisai\ObjectMapper\Meta\Context\MetaFieldContext;
use Orisai\ObjectMapper\Processing\Context\DynamicContext;
use Orisai\ObjectMapper\Processing\Context\ServicesContext;
use Orisai\ObjectMapper\Types\CompoundType;
use function count;
use function sprintf;

/**
 * @implements Rule<CompoundArgs>
 */
abstract class CompoundRule implements Rule
{

	public const Rules = 'rules';

	public function resolveArgs(array $args, MetaFieldContext $context): CompoundArgs
	{
		$checker = new ArgsChecker($args, static::class);
		$checker->checkAllowedArgs([self::Rules]);

		$checker->checkRequiredArg(self::Rules);
		$rules = $checker->checkArray(self::Rules);

		if (count($rules) < 2) {
			throw InvalidArgument::create()
				->withMessage(sprintf(
					'Argument %s given to rule %s expect at least 2 rules',
					self::Rules,
					static::class,
				));
		}

		$resolver = $context->getMetaResolver();

		foreach ($rules as $key => $rule) {
			if (!$rule instanceof RuleDefinition) {
				throw InvalidArgument::create();
			}

			$rules[$key] = $resolver->resolveRuleMeta($rule, $context);
		}

		return new CompoundArgs($rules);
	}

	public function getArgsType(): string
	{
		return CompoundArgs::class;
	}

	public function createType(
		Args $args,
		ServicesContext $services,
		DynamicContext $dynamic
	): CompoundType
	{
		$type = $this->createCompoundType();

		foreach ($args->rules as $key => $nestedRuleMeta) {
			$nestedRule = $services->getRule($nestedRuleMeta->type);
			$nestedRuleArgs = $nestedRuleMeta->args;
			$type->addSubtype(
				$key,
				$nestedRule->createType($nestedRuleArgs, $services, $dynamic->createClone()),
			);
		}

		return $type;
	}

	abstract protected function createCompoundType(): CompoundType;

}
