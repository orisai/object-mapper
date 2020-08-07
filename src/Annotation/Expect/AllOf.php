<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Annotation\Expect;

use Doctrine\Common\Annotations\Annotation\Target;
use Orisai\ObjectMapper\Rules\AllOfRule;
use Orisai\ObjectMapper\Rules\Rule;

/**
 * @Annotation
 * @Target({"PROPERTY", "ANNOTATION"})
 */
final class AllOf extends CompoundRulesAnnotation
{

	/**
	 * @phpstan-return class-string<Rule>
	 */
	public function getType(): string
	{
		return AllOfRule::class;
	}

}
