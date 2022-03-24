<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Attributes\Expect;

use Doctrine\Common\Annotations\Annotation\NamedArgumentConstructor;
use Doctrine\Common\Annotations\Annotation\Target;
use Orisai\ObjectMapper\Rules\ScalarRule;

/**
 * @Annotation
 * @NamedArgumentConstructor()
 * @Target({"PROPERTY", "ANNOTATION"})
 */
final class ScalarValue implements RuleAttribute
{

	public function getType(): string
	{
		return ScalarRule::class;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getArgs(): array
	{
		return [];
	}

}
