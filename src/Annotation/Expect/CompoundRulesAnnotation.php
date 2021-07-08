<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Annotation\Expect;

use Orisai\ObjectMapper\Annotation\AnnotationMetaExtractor;
use Orisai\ObjectMapper\Exception\InvalidAnnotation;
use function count;
use function sprintf;

abstract class CompoundRulesAnnotation implements RuleAnnotation
{

	/** @var array<mixed> */
	private array $rules;

	/**
	 * @param array<RuleAnnotation> $rules
	 */
	public function __construct(array $rules)
	{
		$this->rules = $this->resolveRules($rules);
	}

	/**
	 * @param array<mixed> $rules
	 * @return array<mixed>
	 */
	private function resolveRules(array $rules): array
	{
		if (count($rules) < 2) {
			throw InvalidAnnotation::create()
				->withMessage(sprintf(
					'%s() should contain array of at least two validation rules (%s)',
					static::class,
					RuleAnnotation::class,
				));
		}

		foreach ($rules as $key => $rule) {
			if (!$rule instanceof RuleAnnotation) {
				throw InvalidAnnotation::create()
					->withMessage(sprintf(
						'%s() expects all values to be subtype of %s',
						static::class,
						RuleAnnotation::class,
					));
			}

			$rules[$key] = AnnotationMetaExtractor::extract($rule);
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
