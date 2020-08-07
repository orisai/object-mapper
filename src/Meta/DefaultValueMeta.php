<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Meta;

use Orisai\Exceptions\Logic\InvalidState;
use function count;
use function func_get_args;
use function sprintf;

final class DefaultValueMeta
{

	/** @var mixed */
	private $default;

	private bool $hasDefault = false;

	private function __construct()
	{
	}

	/**
	 * @param mixed $default
	 */
	public static function fromValueOrNothing($default = null): self
	{
		$self = new self();

		if (count(func_get_args()) === 1) {
			$self->hasDefault = true;
			$self->default = $default;
		}

		return $self;
	}

	public function hasValue(): bool
	{
		return $this->hasDefault;
	}

	/**
	 * @return mixed
	 */
	public function getValue()
	{
		if (!$this->hasDefault) {
			throw InvalidState::create()
				->withMessage(sprintf(
					'Check if default value exists with %s::%s',
					self::class,
					'hasValue()',
				));
		}

		return $this->default;
	}

}
