<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Meta;

use Orisai\ObjectMapper\Rules\CompoundRule;
use Orisai\ObjectMapper\Rules\Rule;
use function is_a;
use function is_string;

final class RuleMeta
{

	/** @var class-string<Rule> */
	private string $type;

	/** @var array<mixed> */
	private array $args;

	private function __construct()
	{
	}

	/**
	 * @param array<mixed> $ruleMeta
	 */
	public static function fromArray(array $ruleMeta): self
	{
		$self = new self();
		$self->type = $ruleMeta[MetaSource::OPTION_TYPE];
		$self->args = $ruleMeta[MetaSource::OPTION_ARGS] ?? [];

		return $self;
	}

	/**
	 * @return class-string<Rule>
	 */
	public function getType(): string
	{
		return $this->type;
	}

	/**
	 * @return array<mixed>
	 */
	public function getArgs(): array
	{
		return $this->args;
	}

	/**
	 * @param class-string<Rule>|array<class-string<Rule>> $type
	 */
	public function mayContainRuleType($type): bool
	{
		return $this->mayContainRuleTypeInternal(is_string($type) ? [$type] : $type, $this);
	}

	/**
	 * @param array<class-string<Rule>> $types
	 */
	private function mayContainRuleTypeInternal(array $types, RuleMeta $ruleNode): bool
	{
		$nodeType = $ruleNode->getType();

		foreach ($types as $possibleType) {
			if (is_a($nodeType, $possibleType, true)) {
				return true;
			}
		}

		if (is_a($nodeType, CompoundRule::class, true)) {
			$nodeArgs = $ruleNode->getArgs();

			foreach ($nodeArgs[CompoundRule::RULES] as $nestedRuleMeta) {
				if ($this->mayContainRuleTypeInternal($types, self::fromArray($nestedRuleMeta))) {
					return true;
				}
			}
		}

		return false;
	}

}
