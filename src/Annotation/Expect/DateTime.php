<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Annotation\Expect;

use Doctrine\Common\Annotations\Annotation\Target;
use Orisai\ObjectMapper\Annotation\AutoMappedAnnotation;
use Orisai\ObjectMapper\Rules\DateTimeRule;
use Orisai\ObjectMapper\Rules\Rule;

/**
 * @Annotation
 * @Target({"PROPERTY", "ANNOTATION"})
 * @property-write string|null $format
 * @property-write string $type
 */
final class DateTime implements RuleAnnotation
{

	use AutoMappedAnnotation;

	/**
	 * @phpstan-return class-string<Rule>
	 */
	public function getType(): string
	{
		return DateTimeRule::class;
	}

}
