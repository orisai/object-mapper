<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Rules;

use Attribute;
use Doctrine\Common\Annotations\Annotation\NamedArgumentConstructor;
use Doctrine\Common\Annotations\Annotation\Target;

/**
 * @template-implements RuleDefinition<ObjectRule>
 *
 * @Annotation
 * @NamedArgumentConstructor()
 * @Target({"PROPERTY", "ANNOTATION"})
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
final class ObjectValue implements RuleDefinition
{

	public function getType(): string
	{
		return ObjectRule::class;
	}

	public function getArgs(): array
	{
		return [];
	}

}
