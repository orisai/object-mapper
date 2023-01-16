<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Attributes\Modifiers;

use Attribute;
use Doctrine\Common\Annotations\Annotation\NamedArgumentConstructor;
use Orisai\ObjectMapper\Modifiers\DefaultValueModifier;

/**
 * @Annotation
 * @NamedArgumentConstructor()
 * @Target({"PROPERTY"})
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
final class DefaultValue implements ModifierAttribute
{

	/** @var mixed */
	private $value;

	/**
	 * @param mixed $value
	 */
	public function __construct($value)
	{
		$this->value = $value;
	}

	public function getType(): string
	{
		return DefaultValueModifier::class;
	}

	public function getArgs(): array
	{
		return [
			'value' => $this->value,
		];
	}

}
