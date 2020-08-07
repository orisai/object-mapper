<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Annotation\Expect;

use Doctrine\Common\Annotations\Annotation\Target;
use Orisai\ObjectMapper\Annotation\AutoMappedAnnotation;
use Orisai\ObjectMapper\Rules\Rule;
use Orisai\ObjectMapper\Rules\StructureRule;

/**
 * @Annotation
 * @Target({"PROPERTY", "ANNOTATION"})
 * @property-write string $type
 */
final class Structure implements RuleAnnotation
{

	use AutoMappedAnnotation;

	protected function getMainProperty(): ?string
	{
		return 'type';
	}

	/**
	 * @phpstan-return class-string<Rule>
	 */
	public function getType(): string
	{
		return StructureRule::class;
	}

}
