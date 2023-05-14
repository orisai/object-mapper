<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Rules;

use Attribute;
use Doctrine\Common\Annotations\Annotation\NamedArgumentConstructor;
use Doctrine\Common\Annotations\Annotation\Target;

/**
 * @implements RuleDefinition<InstanceOfRule>
 *
 * @Annotation
 * @NamedArgumentConstructor()
 * @Target({"PROPERTY", "ANNOTATION"})
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
final class InstanceOfValue implements RuleDefinition
{

	/** @var class-string */
	private string $type;

	/**
	 * @param class-string $type
	 */
	public function __construct(string $type)
	{
		$this->type = $type;
	}

	public function getType(): string
	{
		return InstanceOfRule::class;
	}

	public function getArgs(): array
	{
		return [
			'type' => $this->type,
		];
	}

}
