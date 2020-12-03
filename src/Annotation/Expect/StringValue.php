<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Annotation\Expect;

use Doctrine\Common\Annotations\Annotation\Target;
use Orisai\ObjectMapper\Annotation\AutoMappedAnnotation;
use Orisai\ObjectMapper\Rules\Rule;
use Orisai\ObjectMapper\Rules\StringRule;

/**
 * @Annotation
 * @Target({"PROPERTY", "ANNOTATION"})
 * @property-write string|null $pattern
 * @property-write int|null $minLength
 * @property-write int|null $maxLength
 * @property-write bool $notEmpty
 */
final class StringValue implements RuleAnnotation
{

	use AutoMappedAnnotation;

	/**
	 * @return class-string<Rule>
	 */
	public function getType(): string
	{
		return StringRule::class;
	}

}
