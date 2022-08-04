<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Attributes\Expect;

use Attribute;
use Doctrine\Common\Annotations\Annotation\NamedArgumentConstructor;
use Doctrine\Common\Annotations\Annotation\Target;
use Orisai\ObjectMapper\Rules\AllOfRule;

/**
 * @Annotation
 * @NamedArgumentConstructor()
 * @Target({"PROPERTY", "ANNOTATION"})
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
final class AllOf extends CompoundRulesAttribute
{

	public function getType(): string
	{
		return AllOfRule::class;
	}

}
