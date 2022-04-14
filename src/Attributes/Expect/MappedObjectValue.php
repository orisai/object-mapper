<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Attributes\Expect;

use Doctrine\Common\Annotations\Annotation\NamedArgumentConstructor;
use Doctrine\Common\Annotations\Annotation\Target;
use Orisai\ObjectMapper\MappedObject;
use Orisai\ObjectMapper\Rules\MappedObjectRule;

/**
 * @Annotation
 * @NamedArgumentConstructor()
 * @Target({"PROPERTY", "ANNOTATION"})
 */
final class MappedObjectValue implements RuleAttribute
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
