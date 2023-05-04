<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Processing;

use Orisai\Exceptions\Logic\InvalidState;
use function sprintf;

final class Value
{

	private bool $hasValue;

	/** @var mixed */
	private $value;

	/**
	 * @param mixed $value
	 */
	private function __construct(bool $hasValue, $value)
	{
		$this->hasValue = $hasValue;
		$this->value = $value;
	}

	/**
	 * @param mixed $value
	 */
	public static function of($value): self
	{
		return new self(true, $value);
	}

	public static function none(): self
	{
		return new self(false, null);
	}

	public function has(): bool
	{
		return $this->hasValue;
	}

	/**
	 * @return mixed
	 */
	public function get()
	{
		if (!$this->hasValue) {
			throw InvalidState::create()
				->withMessage(sprintf(
					'Check if value exists with %s::%s',
					self::class,
					'has()',
				));
		}

		return $this->value;
	}

}
