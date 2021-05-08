<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Meta;

use Orisai\Exceptions\Logic\InvalidState;
use function sprintf;

final class DefaultValueMeta
{

	/** @var mixed */
	private $value;

	private bool $hasValue;

	private function __construct()
	{
		// Static constructor is required
	}

	/**
	 * @param mixed $default
	 */
	public static function fromValue($default): self
	{
		$self = new self();
		$self->hasValue = true;
		$self->value = $default;

		return $self;
	}

	public static function fromNothing(): self
	{
		$self = new self();
		$self->hasValue = false;

		return $self;
	}

	public function hasValue(): bool
	{
		return $this->hasValue;
	}

	/**
	 * @return mixed
	 */
	public function getValue()
	{
		if (!$this->hasValue) {
			throw InvalidState::create()
				->withMessage(sprintf(
					'Check if default value exists with %s::%s',
					self::class,
					'hasValue()',
				));
		}

		return $this->value;
	}

}
