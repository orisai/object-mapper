<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Modifiers;

use Attribute;
use Doctrine\Common\Annotations\Annotation\NamedArgumentConstructor;
use Doctrine\Common\Annotations\Annotation\Target;

/**
 * @Annotation
 * @NamedArgumentConstructor()
 * @Target({"PROPERTY"})
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
final class FieldName implements ModifierDefinition
{

	/** @var int|string */
	private $name;

	/**
	 * @param int|string $name
	 */
	public function __construct($name)
	{
		$this->name = $name;
	}

	public function getScope(): string
	{
		return $this->getHandler();
	}

	public function getHandler(): string
	{
		return FieldNameModifier::class;
	}

	public function getArgs(): array
	{
		return [
			FieldNameModifier::Name => $this->name,
		];
	}

}
