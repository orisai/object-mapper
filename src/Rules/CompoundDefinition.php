<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Rules;

use Orisai\Exceptions\Logic\InvalidArgument;
use Orisai\ObjectMapper\Args\Args;
use Orisai\ObjectMapper\Meta\Compile\RuleCompileMeta;
use function sprintf;

/**
 * @template-covariant T of Rule
 * @template-implements RuleDefinition<T>
 */
abstract class CompoundDefinition implements RuleDefinition
{

	/** @var array<RuleCompileMeta> */
	private array $rules;

	/**
	 * @param array<RuleDefinition<Rule<Args>>> $rules
	 */
	public function __construct(array $rules)
	{
		$this->rules = $this->resolveRules($rules);
	}

	/**
	 * @param array<mixed> $rules
	 * @return array<RuleCompileMeta>
	 */
	private function resolveRules(array $rules): array
	{
		foreach ($rules as $key => $rule) {
			if (!$rule instanceof RuleDefinition) {
				throw InvalidArgument::create()
					->withMessage(sprintf(
						'%s() expects all values to be subtype of %s',
						static::class,
						RuleDefinition::class,
					));
			}

			$rules[$key] = new RuleCompileMeta($rule->getType(), $rule->getArgs());
		}

		return $rules;
	}

	public function getArgs(): array
	{
		return [
			'rules' => $this->rules,
		];
	}

}
