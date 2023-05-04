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
final class NullValue implements RuleAttribute
{

	private bool $castEmptyString;

	public function __construct(bool $castEmptyString = false)
	{
		$this->castEmptyString = $castEmptyString;
	}

	public function getType(): string
	{
		return NullRule::class;
	}

	public function getArgs(): array
	{
		return [
			'castEmptyString' => $this->castEmptyString,
		];
	}

}
