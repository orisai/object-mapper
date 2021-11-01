<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Annotation\Expect;

use Doctrine\Common\Annotations\Annotation\NamedArgumentConstructor;
use Doctrine\Common\Annotations\Annotation\Target;
use Orisai\ObjectMapper\Rules\ScalarRule;

/**
 * @Annotation
 * @NamedArgumentConstructor()
 * @Target({"PROPERTY", "ANNOTATION"})
 */
final class ScalarValue implements RuleAnnotation
{

	public function getType(): string
	{
		return ScalarRule::class;
	}

	/**
	 * @return array<mixed>
	 */
	public function getArgs(): array
	{
		return [];
	}

}
