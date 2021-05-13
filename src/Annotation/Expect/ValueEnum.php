<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Annotation\Expect;

use Doctrine\Common\Annotations\Annotation\Target;
use Orisai\ObjectMapper\Annotation\AutoMappedAnnotation;
use Orisai\ObjectMapper\Rules\Rule;
use Orisai\ObjectMapper\Rules\ValueEnumRule;

/**
 * @Annotation
 * @Target({"PROPERTY", "ANNOTATION"})
 * @property-write array<mixed> $values
 * @property-write bool $useKeys
 */
final class ValueEnum implements RuleAnnotation
{

	use AutoMappedAnnotation;

	protected function getMainProperty(): string
	{
		return 'values';
	}

	/**
	 * @return class-string<Rule>
	 */
	public function getType(): string
	{
		return ValueEnumRule::class;
	}

}
