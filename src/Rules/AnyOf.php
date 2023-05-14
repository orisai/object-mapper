<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Rules;

use Attribute;
use Doctrine\Common\Annotations\Annotation\NamedArgumentConstructor;
use Doctrine\Common\Annotations\Annotation\Target;

/**
 * @extends CompoundRulesDefinition<AnyOfRule>
 *
 * @Annotation
 * @NamedArgumentConstructor()
 * @Target({"PROPERTY", "ANNOTATION"})
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
final class AnyOf extends CompoundRulesDefinition
{

	public function getType(): string
	{
		return AnyOfRule::class;
	}

}
