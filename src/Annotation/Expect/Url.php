<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Annotation\Expect;

use Doctrine\Common\Annotations\Annotation\Target;
use Orisai\ObjectMapper\Annotation\AutoMappedAnnotation;
use Orisai\ObjectMapper\Rules\Rule;
use Orisai\ObjectMapper\Rules\UrlRule;

/**
 * @Annotation
 * @Target({"PROPERTY", "ANNOTATION"})
 */
final class Url implements RuleAnnotation
{

	use AutoMappedAnnotation;

	/**
	 * @phpstan-return class-string<Rule>
	 */
	public function getType(): string
	{
		return UrlRule::class;
	}

}
