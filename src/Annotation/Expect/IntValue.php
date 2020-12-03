<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Annotation\Expect;

use Doctrine\Common\Annotations\Annotation\Target;
use Orisai\ObjectMapper\Annotation\AutoMappedAnnotation;
use Orisai\ObjectMapper\Rules\IntRule;
use Orisai\ObjectMapper\Rules\Rule;

/**
 * @Annotation
 * @Target({"PROPERTY", "ANNOTATION"})
 * @property-write int|null $min
 * @property-write int|null $max
 * @property-write bool $unsigned
 * @property-write bool $castNumericString
 */
final class IntValue implements RuleAnnotation
{

	use AutoMappedAnnotation;

	/**
	 * @return class-string<Rule>
	 */
	public function getType(): string
	{
		return IntRule::class;
	}

}
