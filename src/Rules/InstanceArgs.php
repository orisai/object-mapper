<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Rules;

use Orisai\Exceptions\Logic\InvalidArgument;
use Orisai\ObjectMapper\Meta\Args;
use function array_key_exists;
use function sprintf;

final class InstanceArgs implements Args
{

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

		if (array_key_exists(InstanceRule::TYPE, $args)) {
			$self->type = $args[InstanceRule::TYPE];
		} else {
			throw InvalidArgument::create()
				->withMessage(sprintf('Key "%s" is required', InstanceRule::TYPE));
		}

		return $self;
	}

}
