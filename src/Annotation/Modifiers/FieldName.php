<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Annotation\Modifiers;

use Doctrine\Common\Annotations\Annotation\NamedArgumentConstructor;
use Orisai\ObjectMapper\Modifiers\FieldNameModifier;

/**
 * @Annotation
 * @NamedArgumentConstructor()
 * @Target({"PROPERTY"})
 */
final class FieldName implements ModifierAnnotation
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

	/**
	 * @return array<mixed>
	 */
	public function getArgs(): array
	{
		return [
			'name' => $this->name,
		];
	}

}
