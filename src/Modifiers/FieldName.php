<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Modifiers;

use Attribute;
use Doctrine\Common\Annotations\Annotation\NamedArgumentConstructor;

/**
 * @implements ModifierDefinition<FieldNameModifier>
 *
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

	public function getType(): string
	{
		return FieldNameModifier::class;
	}

	public function getArgs(): array
	{
		return [
			'name' => $this->name,
		];
	}

}
