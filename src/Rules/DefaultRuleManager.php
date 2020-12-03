<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Rules;

use Orisai\Exceptions\Logic\InvalidArgument;
use function in_array;
use function sprintf;

final class DefaultRuleManager implements RuleManager
{

	/** @var array<class-string<Rule>, Rule> */
	private array $instances = [];

	public function getRule(string $rule): Rule
	{
		if (isset($this->instances[$rule])) {
			return $this->instances[$rule];
		}

		if (in_array($rule, self::DEFAULT_RULES, true)) {
			return $this->instances[$rule] = new $rule();
		}

		throw InvalidArgument::create()
			->withMessage(sprintf(
				'Rule `%s` does not exist.',
				$rule,
			));
	}

	/**
	 * @param class-string<Rule> $type
	 */
	public function addRule(string $type, Rule $rule): void
	{
		$this->instances[$type] = $rule;
	}

}
