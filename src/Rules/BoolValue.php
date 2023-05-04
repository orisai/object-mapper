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
final class BoolValue implements RuleDefinition
{

	private bool $castBoolLike;

	public function __construct(bool $castBoolLike = false)
	{
		$this->castBoolLike = $castBoolLike;
	}

	public function getType(): string
	{
		return BoolRule::class;
	}

	public function getArgs(): array
	{
		return [
			'castBoolLike' => $this->castBoolLike,
		];
	}

}
