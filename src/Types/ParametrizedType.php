<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Types;

use Orisai\Exceptions\Logic\InvalidState;
use function array_key_exists;
use function sprintf;

abstract class ParametrizedType implements Type
{

	/** @var array<int|string, TypeParameter> */
	private array $parameters = [];

	/**
	 * @return array<int|string, TypeParameter>
	 */
	public function getParameters(): array
	{
		return $this->parameters;
	}

	/**
	 * @param int|string $key
	 */
	public function getParameter($key): TypeParameter
	{
		if (!$this->hasParameter($key)) {
			throw InvalidState::create()
				->withMessage(sprintf(
					'Cannot get parameter `%s` because it was never set',
					$key,
				));
		}

		return $this->parameters[$key];
	}

	/**
	 * @param int|string $key
	 */
	public function hasParameter($key): bool
	{
		return array_key_exists($key, $this->parameters);
	}

	/**
	 * @param int|string $key
	 * @param mixed $value
	 */
	public function addKeyValueParameter($key, $value): void
	{
		$this->parameters[$key] = TypeParameter::fromKeyAndValue($key, $value);
	}

	/**
	 * @param int|string $key
	 */
	public function addKeyParameter($key): void
	{
		$this->parameters[$key] = TypeParameter::fromKey($key);
	}

	/**
	 * @param int|string $key
	 */
	public function markParameterInvalid($key): void
	{
		$this->getParameter($key)->markInvalid();
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
		foreach ($this->parameters as $parameter) {
			if ($parameter->isInvalid()) {
				return true;
			}
		}

		return false;
	}

}
