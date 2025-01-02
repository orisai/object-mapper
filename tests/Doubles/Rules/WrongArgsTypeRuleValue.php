<?php declare(strict_types = 1);

namespace Tests\Orisai\ObjectMapper\Doubles\Rules;

use Attribute;
use Doctrine\Common\Annotations\Annotation\NamedArgumentConstructor;
use Orisai\ObjectMapper\Rules\RuleDefinition;

/**
 * @Annotation
 * @NamedArgumentConstructor()
 * @Target({"PROPERTY"})
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
final class WrongArgsTypeRuleValue implements RuleDefinition
{

	public function getType(): string
	{
		return WrongArgsTypeRule::class;
	}

	public function getArgs(): array
	{
		return [];
	}

}
