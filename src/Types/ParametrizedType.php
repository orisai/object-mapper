<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Types;

use Orisai\Exceptions\Logic\InvalidState;
use function array_key_exists;
use function in_array;
use function sprintf;

abstract class ParametrizedType implements Type
{

	/** @var array<mixed> */
	protected array $parameters;

	/** @var array<int|string> */
	protected array $invalidParameters = [];

	/**
	 * @return array<mixed>
	 */
	public function getParameters(): array
	{
		return $this->parameters;
	}

	/**
	 * @param string|int $key
	 */
	public function markParameterInvalid($key): void
	{
		if (!array_key_exists($key, $this->parameters)) {
			throw InvalidState::create()
				->withMessage(sprintf(
					'Cannot mark parameter %s invalid because it was never set',
					$key,
				));
		}

		$this->invalidParameters[] = $key;
	}

	/**
	 * @param array<int|string> $keys
	 */
	public function markParametersInvalid(array $keys): void
	{
		foreach ($keys as $key) {
			$this->markParameterInvalid($key);
		}
	}

	public function hasInvalidParameters(): bool
	{
		return $this->invalidParameters !== [];
	}

	/**
	 * @param string|int $key
	 */
	public function isParameterInvalid($key): bool
	{
		return in_array($key, $this->invalidParameters, true);
	}

}
