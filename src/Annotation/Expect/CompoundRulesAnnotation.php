<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Annotation\Expect;

use Orisai\ObjectMapper\Annotation\AnnotationMetaExtractor;
use Orisai\ObjectMapper\Annotation\AutoMappedAnnotation;
use Orisai\ObjectMapper\Annotation\BaseAnnotation;
use Orisai\ObjectMapper\Exception\InvalidAnnotation;
use function count;
use function is_array;
use function sprintf;

/**
 * @property-write array<RuleAnnotation> $rules
 */
abstract class CompoundRulesAnnotation implements RuleAnnotation
{

	use AutoMappedAnnotation;

	protected function getMainProperty(): ?string
	{
		return 'rules';
	}

	/**
	 * @param array<mixed> $args
	 * @return array<mixed>
	 */
	protected function processArgs(array $args): array
	{
		$rules = $args['rules'] ?? null;

		if ($rules instanceof BaseAnnotation) {
			$rules = [
				$rules,
			];
		}

		if (!is_array($rules) || count($rules) < 2) {
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

		$args['rules'] = $rules;

		return $args;
	}

}
