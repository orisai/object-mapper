<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Annotation\Expect;

use Doctrine\Common\Annotations\Annotation\Target;
use Orisai\ObjectMapper\Annotation\AnnotationMetaExtractor;
use Orisai\ObjectMapper\Annotation\BaseAnnotation;
use Orisai\ObjectMapper\Rules\ArrayOfRule;
use Orisai\ObjectMapper\Rules\Rule;
use function array_key_exists;

/**
 * @Annotation
 * @Target({"PROPERTY", "ANNOTATION"})
 * @property-write RuleAnnotation|null $keyType
 */
final class ArrayOf extends MultiValueRuleAnnotation
{

	/**
	 * @phpstan-return class-string<Rule>
	 */
	public function getType(): string
	{
		return ArrayOfRule::class;
	}

	/**
	 * @param array<mixed> $args
	 * @return array<mixed>
	 */
	protected function processArgs(array $args): array
	{
		$args = parent::processArgs($args);

		if (array_key_exists('keyType', $args) && $args['keyType'] instanceof BaseAnnotation) {
			$args['keyType'] = AnnotationMetaExtractor::extract($args['keyType']);
		}

		return $args;
	}

}
