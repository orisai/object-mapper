<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Attributes\Expect;

use Orisai\Exceptions\Logic\InvalidArgument;
use Orisai\ObjectMapper\Meta\Compile\RuleCompileMeta;
use function count;
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
		if (count($rules) < 2) {
			throw InvalidArgument::create()
				->withMessage(sprintf(
					'%s() should contain array of at least two validation rules (%s)',
					static::class,
					RuleAttribute::class,
				));
		}

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

	/**
	 * @return array<mixed>
	 */
	public function getArgs(): array
	{
		return [
			'rules' => $this->rules,
		];
	}

}
