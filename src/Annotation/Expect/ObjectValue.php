<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Annotation\Expect;

use Doctrine\Common\Annotations\Annotation\NamedArgumentConstructor;
use Doctrine\Common\Annotations\Annotation\Target;
use Orisai\ObjectMapper\Rules\ObjectRule;

/**
 * @Annotation
 * @NamedArgumentConstructor()
 * @Target({"PROPERTY", "ANNOTATION"})
 */
final class ObjectValue implements RuleAnnotation
{

	public function getType(): string
	{
		return ObjectRule::class;
	}

	/**
	 * @return array<mixed>
	 */
	public function getArgs(): array
	{
		return [];
	}

}
