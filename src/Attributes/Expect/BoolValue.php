<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Attributes\Expect;

use Doctrine\Common\Annotations\Annotation\NamedArgumentConstructor;
use Doctrine\Common\Annotations\Annotation\Target;
use Orisai\ObjectMapper\Rules\BoolRule;

/**
 * @Annotation
 * @NamedArgumentConstructor()
 * @Target({"PROPERTY", "ANNOTATION"})
 */
final class BoolValue implements RuleAttribute
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
