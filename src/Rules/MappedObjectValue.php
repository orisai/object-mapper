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
final class MappedObjectValue extends RuleDefinition
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

	public function getHandler(): string
	{
		return MappedObjectRule::class;
	}

	public function getArgs(): array
	{
		return [
			MappedObjectRule::ClassName => $this->class,
		];
	}

}
