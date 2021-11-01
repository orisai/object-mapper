<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Annotation\Expect;

use Doctrine\Common\Annotations\Annotation\NamedArgumentConstructor;
use Doctrine\Common\Annotations\Annotation\Target;
use Orisai\ObjectMapper\Rules\AnyOfRule;

/**
 * @Annotation
 * @NamedArgumentConstructor()
 * @Target({"PROPERTY", "ANNOTATION"})
 */
final class AnyOf extends CompoundRulesAnnotation
{

	public function getType(): string
	{
		return AnyOfRule::class;
	}

}
