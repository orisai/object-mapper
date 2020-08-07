<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Rules;

use Orisai\Exceptions\Logic\InvalidArgument;
use Orisai\ObjectMapper\Meta\Args;
use Orisai\ObjectMapper\ValueObject;
use function array_key_exists;
use function sprintf;

final class StructureArgs implements Args
{

	/** @phpstan-var class-string<ValueObject> */
	public string $type;

	private function __construct()
	{
	}

	/**
	 * @param array<mixed> $args
	 */
	public static function fromArray(array $args): self
	{
		$self = new self();

		if (array_key_exists(StructureRule::TYPE, $args)) {
			$self->type = $args[StructureRule::TYPE];
		} else {
			throw InvalidArgument::create()
				->withMessage(sprintf('Key "%s" is required', StructureRule::TYPE));
		}

		return $self;
	}

	/**
	 * @phpstan-param class-string<ValueObject> $class
	 */
	public static function fromClass(string $class): self
	{
		$self = new self();
		$self->type = $class;

		return $self;
	}

}
