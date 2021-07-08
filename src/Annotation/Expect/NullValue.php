<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Annotation\Expect;

use Doctrine\Common\Annotations\Annotation\NamedArgumentConstructor;
use Doctrine\Common\Annotations\Annotation\Target;
use Orisai\ObjectMapper\Rules\NullRule;
use Orisai\ObjectMapper\Rules\Rule;

/**
 * @Annotation
 * @NamedArgumentConstructor()
 * @Target({"PROPERTY", "ANNOTATION"})
 */
final class NullValue implements RuleAnnotation
{

	private bool $castEmptyString;

	public function __construct(bool $castEmptyString = false)
	{
		$this->castEmptyString = $castEmptyString;
	}

	/**
	 * @return class-string<Rule>
	 */
	public function getType(): string
	{
		return NullRule::class;
	}

	/**
	 * @return array<mixed>
	 */
	public function getArgs(): array
	{
		return [
			'castEmptyString' => $this->castEmptyString,
		];
	}

}
