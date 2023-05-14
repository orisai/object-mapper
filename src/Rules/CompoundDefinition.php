<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Rules;

use Orisai\Exceptions\Logic\InvalidArgument;
use Orisai\ObjectMapper\Args\Args;
use Orisai\ObjectMapper\Meta\Compile\RuleCompileMeta;
use function sprintf;

/**
 * @template-covariant T of CompoundRule
 * @implements RuleDefinition<T>
 */
abstract class CompoundDefinition implements RuleDefinition
{

	/** @var array<RuleCompileMeta> */
	private array $rules;

	/**
	 * @param array<RuleDefinition<Rule<Args>>> $definitions
	 */
	public function __construct(array $definitions)
	{
		$this->rules = $this->resolveRules($definitions);
	}

	/**
	 * @param array<mixed> $definitions
	 * @return array<RuleCompileMeta>
	 */
	private function resolveRules(array $definitions): array
	{
		foreach ($definitions as $key => $definition) {
			if (!$definition instanceof RuleDefinition) {
				throw InvalidArgument::create()
					->withMessage(sprintf(
						'%s() expects all values to be subtype of %s',
						static::class,
						RuleDefinition::class,
					));
			}

			$definitions[$key] = new RuleCompileMeta($definition->getType(), $definition->getArgs());
		}

		return $definitions;
	}

	public function getArgs(): array
	{
		return [
			'rules' => $this->rules,
		];
	}

}
