<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Annotation\Expect;

use Doctrine\Common\Annotations\Annotation\Target;
use Orisai\ObjectMapper\Annotation\AutoMappedAnnotation;
use Orisai\ObjectMapper\Rules\MixedRule;
use Orisai\ObjectMapper\Rules\Rule;

/**
 * @Annotation
 * @Target({"PROPERTY", "ANNOTATION"})
 */
final class MixedValue implements RuleAnnotation
{

	use AutoMappedAnnotation;

	/**
	 * @return class-string<Rule>
	 */
	public function getType(): string
	{
		return MixedRule::class;
	}

}
