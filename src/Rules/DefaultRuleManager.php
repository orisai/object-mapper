<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Rules;

use Orisai\Exceptions\Logic\InvalidArgument;
use Orisai\ObjectMapper\Args\Args;
use function get_class;
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

		if (in_array($rule, self::DefaultRules, true)) {
			return $this->instances[$rule] = new $rule();
		}

		throw InvalidArgument::create()
			->withMessage(sprintf(
				'Rule `%s` does not exist.',
				$rule,
			));
	}

	/**
	 * @param Rule<Args> $rule
	 */
	public function addRule(Rule $rule): void
	{
		$this->instances[get_class($rule)] = $rule;
	}

}
