<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Rules;

use Orisai\Exceptions\Logic\InvalidArgument;
use Orisai\ObjectMapper\Meta\Compile\RuleCompileMeta;
use function sprintf;

abstract class CompoundRulesAttribute implements RuleAttribute
{

	/** @var array<RuleCompileMeta> */
	private array $rules;

	/**
	 * @param array<RuleAttribute> $rules
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
			if (!$rule instanceof RuleAttribute) {
				throw InvalidArgument::create()
					->withMessage(sprintf(
						'%s() expects all values to be subtype of %s',
						static::class,
						RuleAttribute::class,
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
