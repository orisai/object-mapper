<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Types;

use Orisai\Exceptions\Logic\InvalidState;
use function sprintf;

final class TypeParameter
{

	/** @var int|string */
	private $key;

	/** @var mixed */
	private $value;

	private bool $hasValue;

	private bool $isInvalid = false;

	/**
	 * @param int|string $key
	 */
	public static function fromKey($key): self
	{
		$self = new self();
		$self->key = $key;
		$self->hasValue = false;

		return $self;
	}

	/**
	 * @param int|string $key
	 * @param mixed $value
	 */
	public static function fromKeyAndValue($key, $value): self
	{
		$self = new self();
		$self->key = $key;
		$self->value = $value;
		$self->hasValue = true;

		return $self;
	}

	/**
	 * @return int|string
	 */
	public function getKey()
	{
		return $this->key;
	}

	/**
	 * @return mixed
	 */
	public function getValue()
	{
		if (!$this->hasValue()) {
			throw InvalidState::create()
				->withMessage(sprintf(
					'Cannot access value of parameter which does not have one. Check with `%s->hasValue()`.',
					self::class,
				));
		}

		return $this->value;
	}

	public function hasValue(): bool
	{
		return $this->hasValue;
	}

	public function markInvalid(): void
	{
		$this->isInvalid = true;
	}

	public function isInvalid(): bool
	{
		return $this->isInvalid;
	}

}
