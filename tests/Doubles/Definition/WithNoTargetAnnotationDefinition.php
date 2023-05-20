<?php declare(strict_types = 1);

namespace Tests\Orisai\ObjectMapper\Doubles\Definition;

use Doctrine\Common\Annotations\Annotation\NamedArgumentConstructor;
use Orisai\ObjectMapper\Rules\MixedRule;
use Orisai\ObjectMapper\Rules\RuleDefinition;

/**
 * @Annotation
 * @NamedArgumentConstructor()
 */
final class WithNoTargetAnnotationDefinition implements RuleDefinition
{

	public function getType(): string
	{
		return MixedRule::class;
	}

	public function getArgs(): array
	{
		return [];
	}

}
