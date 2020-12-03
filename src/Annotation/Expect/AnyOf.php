<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Annotation\Expect;

use Doctrine\Common\Annotations\Annotation\Target;
use Orisai\ObjectMapper\Rules\AnyOfRule;
use Orisai\ObjectMapper\Rules\Rule;

/**
 * @Annotation
 * @Target({"PROPERTY", "ANNOTATION"})
 */
final class AnyOf extends CompoundRulesAnnotation
{

	/**
	 * @return class-string<Rule>
	 */
	public function getType(): string
	{
		return AnyOfRule::class;
	}

}
