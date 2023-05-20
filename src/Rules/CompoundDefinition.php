<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Rules;

use Orisai\Exceptions\Logic\InvalidArgument;
use Orisai\ObjectMapper\Meta\Compile\RuleCompileMeta;
use function get_debug_type;

abstract class CompoundDefinition implements RuleDefinition
{

	/** @var array<RuleCompileMeta> */
	private array $rules;

	/**
	 * @param array<RuleDefinition> $definitions
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
				$selfClass = static::class;
				$definitionClass = RuleDefinition::class;
				$givenType = get_debug_type($definition);

				throw InvalidArgument::create()
					->withMessage("'$selfClass(definitions)' expects all values to be subtype"
						. " of '$definitionClass', '$givenType' given.");
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
