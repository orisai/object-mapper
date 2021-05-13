<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Annotation\Expect;

use Orisai\ObjectMapper\Annotation\AnnotationMetaExtractor;
use Orisai\ObjectMapper\Annotation\AutoMappedAnnotation;
use Orisai\ObjectMapper\Annotation\BaseAnnotation;
use function array_key_exists;

/**
 * @property-write RuleAnnotation $itemRule
 * @property-write int|null $minItems
 * @property-write int|null $maxItems
 * @property-write bool $mergeDefaults
 */
abstract class MultiValueRuleAnnotation implements RuleAnnotation
{

	use AutoMappedAnnotation;

	protected function getMainProperty(): string
	{
		return 'itemRule';
	}

	/**
	 * @param array<mixed> $args
	 * @return array<mixed>
	 */
	protected function resolveArgs(array $args): array
	{
		if (array_key_exists('itemRule', $args) && $args['itemRule'] instanceof BaseAnnotation) {
			$args['itemRule'] = AnnotationMetaExtractor::extract($args['itemRule']);
		}

		return $args;
	}

}
