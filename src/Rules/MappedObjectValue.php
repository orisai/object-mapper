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
	private string $class;

	/**
	 * @param class-string<MappedObject> $class
	 */
	public function __construct(string $class)
	{
		$this->class = $class;
	}

	public function getType(): string
	{
		return MappedObjectRule::class;
	}

	public function getArgs(): array
	{
		return [
			'class' => $this->class,
		];
	}

}
