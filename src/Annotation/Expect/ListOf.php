<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Annotation\Expect;

use Doctrine\Common\Annotations\Annotation\NamedArgumentConstructor;
use Doctrine\Common\Annotations\Annotation\Target;
use Orisai\ObjectMapper\Rules\ListOfRule;

/**
 * @Annotation
 * @NamedArgumentConstructor()
 * @Target({"PROPERTY", "ANNOTATION"})
 */
final class ListOf extends MultiValueRuleAnnotation
{

	public function getType(): string
	{
		return ListOfRule::class;
	}

}
