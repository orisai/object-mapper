<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Rules;

use Orisai\Exceptions\Logic\InvalidArgument;
use Orisai\ObjectMapper\Args\Args;
use Orisai\ObjectMapper\MappedObject;
use function array_key_exists;
use function sprintf;

final class StructureArgs implements Args
{

	/** @var class-string<MappedObject> */
	public string $type;

	private function __construct()
	{
		// Static constructor is required
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
	 * @param class-string<MappedObject> $class
	 */
	public static function fromClass(string $class): self
	{
		$self = new self();
		$self->type = $class;

		return $self;
	}

}
