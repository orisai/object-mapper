<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Annotation\Expect;

use Doctrine\Common\Annotations\Annotation\Target;
use Orisai\ObjectMapper\Annotation\AutoMappedAnnotation;
use Orisai\ObjectMapper\Rules\FloatRule;
use Orisai\ObjectMapper\Rules\Rule;

/**
 * @Annotation
 * @Target({"PROPERTY", "ANNOTATION"})
 * @property-write float|null $min
 * @property-write float|null $max
 * @property-write bool $unsigned
 * @property-write bool $castNumericString
 */
final class FloatValue implements RuleAnnotation
{

	use AutoMappedAnnotation;

	/**
	 * @return class-string<Rule>
	 */
	public function getType(): string
	{
		return FloatRule::class;
	}

}
