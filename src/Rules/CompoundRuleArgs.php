<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Rules;

use Orisai\Exceptions\Logic\InvalidArgument;
use Orisai\ObjectMapper\Args\Args;
use Orisai\ObjectMapper\Meta\Runtime\RuleRuntimeMeta;
use function array_key_exists;
use function sprintf;

/**
 * @internal
 */
final class CompoundRuleArgs implements Args
{

	/** @var array<RuleRuntimeMeta> */
	public array $rules;

	private function __construct()
	{
		// Static constructor is required
	}

	/**
	 * @param array<mixed> $args
	 */
	public static function fromArray(array $args): self
	{
		$self = new self();

		if (array_key_exists(CompoundRule::RULES, $args)) {
			$rules = [];

			foreach ($args[CompoundRule::RULES] as $key => $rule) {
				$rules[$key] = $rule;
			}

			$self->rules = $rules;
		} else {
			throw InvalidArgument::create()
				->withMessage(sprintf('Key "%s" is required', CompoundRule::RULES));
		}

		return $self;
	}

}
