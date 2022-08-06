<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Rules;

interface RuleManager
{

	public const DefaultRules = [
		AllOfRule::class,
		AnyOfRule::class,
		ArrayEnumRule::class,
		ArrayOfRule::class,
		BackedEnumRule::class,
		BoolRule::class,
		DateTimeRule::class,
		FloatRule::class,
		InstanceRule::class,
		IntRule::class,
		ListOfRule::class,
		MappedObjectRule::class,
		MixedRule::class,
		NullRule::class,
		ObjectRule::class,
		ScalarRule::class,
		StringRule::class,
		UrlRule::class,
	];

	/**
	 * @template T of Rule
	 * @param class-string<T> $rule
	 * @return T
	 */
	public function getRule(string $rule): Rule;

}
