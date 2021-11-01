<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Bridge\NetteDI;

use Nette\DI\Container;
use Orisai\Exceptions\Logic\InvalidArgument;
use Orisai\ObjectMapper\Meta\Args;
use Orisai\ObjectMapper\Rules\Rule;
use Orisai\ObjectMapper\Rules\RuleManager;
use function assert;
use function in_array;
use function sprintf;

final class LazyRuleManager implements RuleManager
{

	private Container $container;

	/** @var array<class-string<Rule>, string> */
	private array $services = [];

	/** @var array<class-string<Rule>, Rule> */
	private array $instances = [];

	public function __construct(Container $container)
	{
		$this->container = $container;
	}

	public function getRule(string $rule): Rule
	{
		if (isset($this->instances[$rule])) {
			return $this->instances[$rule];
		}

		if (in_array($rule, self::DEFAULT_RULES, true)) {
			return $this->instances[$rule] = new $rule();
		}

		if (isset($this->services[$rule])) {
			$instance = $this->container->getService($this->services[$rule]);
			assert($instance instanceof Rule);

			return $this->instances[$rule] = $instance;
		}

		throw InvalidArgument::create()
			->withMessage(sprintf(
				'Rule `%s` does not exist.',
				$rule,
			));
	}

	/**
	 * @param class-string<Rule<Args>> $type
	 */
	public function addLazyRule(string $type, string $serviceName): void
	{
		$this->services[$type] = $serviceName;
	}

}
