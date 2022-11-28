<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Rules;

use Orisai\Exceptions\Logic\InvalidArgument;
use Orisai\ObjectMapper\Args\Args;
use Orisai\ObjectMapper\Args\ArgsChecker;
use Orisai\ObjectMapper\Context\RuleArgsContext;
use Orisai\ObjectMapper\Context\TypeContext;
use Orisai\ObjectMapper\Meta\Compile\RuleCompileMeta;
use Orisai\ObjectMapper\Meta\Runtime\RuleRuntimeMeta;
use Orisai\ObjectMapper\PhpTypes\Node;
use Orisai\ObjectMapper\Types\CompoundType;
use function count;
use function sprintf;

/**
 * @phpstan-implements Rule<CompoundArgs>
 */
abstract class CompoundRule implements Rule
{

	/** @internal */
	public const Rules = 'rules';

	public function resolveArgs(array $args, RuleArgsContext $context): CompoundArgs
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
			if (!$rule instanceof RuleCompileMeta) {
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

	/**
	 * @param CompoundArgs $args
	 */
	public function createType(Args $args, TypeContext $context): CompoundType
	{
		$type = $this->createCompoundType();

		foreach ($args->rules as $key => $nestedRuleMeta) {
			[$subNodeRule, $subNodeArgs] = $this->getSubNodeRuleArgs($nestedRuleMeta, $context);
			$type->addSubtype($key, $subNodeRule->createType($subNodeArgs, $context));
		}

		return $type;
	}

	abstract protected function createCompoundType(): CompoundType;

	/**
	 * @param RuleRuntimeMeta<Args> $meta
	 * @return array{Rule<Args>, Args}
	 */
	private function getSubNodeRuleArgs(RuleRuntimeMeta $meta, TypeContext $context): array
	{
		$rule = $context->getRule($meta->getType());
		$args = $meta->getArgs();

		return [$rule, $args];
	}

	/**
	 * @return array<int, Node>
	 */
	protected function getExpectedInputTypeNodes(CompoundRuleArgs $args, TypeContext $context): array
	{
		$nodes = [];
		foreach ($args->rules as $ruleMeta) {
			[$subNodeRule, $subNodeArgs] = $this->getSubNodeRuleArgs($ruleMeta, $context);
			$nodes[] = $subNodeRule->getExpectedInputType($subNodeArgs, $context);
		}

		return $nodes;
	}

	/**
	 * @return array<int, Node>
	 */
	protected function getReturnTypeNodes(CompoundRuleArgs $args, TypeContext $context): array
	{
		$nodes = [];
		foreach ($args->rules as $ruleMeta) {
			[$subNodeRule, $subNodeArgs] = $this->getSubNodeRuleArgs($ruleMeta, $context);
			$nodes[] = $subNodeRule->getReturnType($subNodeArgs, $context);
		}

		return $nodes;
	}

}
