<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Annotation\Expect;

use Doctrine\Common\Annotations\Annotation\NamedArgumentConstructor;
use Doctrine\Common\Annotations\Annotation\Target;
use Orisai\ObjectMapper\Rules\InstanceRule;
use Orisai\ObjectMapper\Rules\Rule;

/**
 * @Annotation
 * @NamedArgumentConstructor()
 * @Target({"PROPERTY", "ANNOTATION"})
 */
final class InstanceValue implements RuleAnnotation
{

	/** @var class-string */
	private string $type;

	/**
	 * @phpstan-param class-string $type
	 */
	public function __construct(string $type)
	{
		$this->type = $type;
	}

	/**
	 * @return class-string<Rule>
	 */
	public function getType(): string
	{
		return InstanceRule::class;
	}

	/**
	 * @return array<mixed>
	 */
	public function getArgs(): array
	{
		return [
			'type' => $this->type,
		];
	}

}
