<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Meta;

use Orisai\Exceptions\Logic\InvalidState;
use function is_object;
use function serialize;
use function sprintf;
use function unserialize;

final class DefaultValueMeta
{

	/** @var mixed */
	private $value;

	private bool $hasValue;

	private bool $isSerialized;

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

		if (is_object($default)) {
			$self->value = serialize($default);
			$self->isSerialized = true;
		} else {
			$self->value = $default;
			$self->isSerialized = false;
		}

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

		if ($this->isSerialized) {
			return unserialize($this->value);
		}

		return $this->value;
	}

}
