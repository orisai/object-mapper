<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Rules;

use Attribute;
use Doctrine\Common\Annotations\Annotation\NamedArgumentConstructor;
use Doctrine\Common\Annotations\Annotation\Target;
use Orisai\ObjectMapper\MappedObject;

/**
 * @Annotation
 * @NamedArgumentConstructor()
 * @Target({"PROPERTY", "ANNOTATION"})
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
final class MappedObjectValue implements RuleDefinition
{

	/** @var class-string<MappedObject> */
	private string $type;

	/**
	 * @param class-string<MappedObject> $type
	 */
	public function __construct(string $type)
	{
		$this->type = $type;
	}

	public function getType(): string
	{
		return MappedObjectRule::class;
	}

	public function getArgs(): array
	{
		return [
			'type' => $this->type,
		];
	}

}
