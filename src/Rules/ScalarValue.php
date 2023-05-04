<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Rules;

use Attribute;
use Doctrine\Common\Annotations\Annotation\NamedArgumentConstructor;
use Doctrine\Common\Annotations\Annotation\Target;

/**
 * @Annotation
 * @NamedArgumentConstructor()
 * @Target({"PROPERTY", "ANNOTATION"})
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
final class ScalarValue implements RuleAttribute
{

	public function getType(): string
	{
		return ScalarRule::class;
	}

	public function getArgs(): array
	{
		return [];
	}

}
