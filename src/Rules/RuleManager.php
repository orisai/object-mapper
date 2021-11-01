<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Rules;

interface RuleManager
{

	public const DEFAULT_RULES = [
		AllOfRule::class,
		AnyOfRule::class,
		ArrayOfRule::class,
		BoolRule::class,
		DateTimeRule::class,
		FloatRule::class,
		InstanceRule::class,
		IntRule::class,
		ListOfRule::class,
		MixedRule::class,
		NullRule::class,
		ObjectRule::class,
		ScalarRule::class,
		StringRule::class,
		StructureRule::class,
		UrlRule::class,
		ValueEnumRule::class,
	];

	/**
	 * @template T of Rule
	 * @param class-string<T> $rule
	 * @return T
	 */
	public function getRule(string $rule): Rule;

}
