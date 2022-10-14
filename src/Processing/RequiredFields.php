<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Processing;

use ValueError;

final class RequiredFields
{

	private const
		NonDefault = 1,
		All = 2,
		None = 3;

	private const ValuesAndNames = [
		self::NonDefault => 'nonDefault',
		self::All => 'all',
		self::None => 'none',
	];

	/** @readonly */
	public string $name;

	/** @readonly */
	public int $value;

	/** @var array<string, self> */
	private static array $instances = [];

	private function __construct(string $name, int $value)
	{
		$this->name = $name;
		$this->value = $value;
	}

	/**
	 * Default option, only fields without default value are required
	 */
	public static function nonDefault(): self
	{
		return self::from(self::NonDefault);
	}

	/**
	 * All fields are required
	 * Defaults are used only by rules which merge them
	 * Useful for PUT request (full entity replace) - user must send all fields to prevent accidental override by default value
	 */
	public static function all(): self
	{
		return self::from(self::All);
	}

	/**
	 * No fields are required
	 * Fields which are not sent are unset so isset could be used to check if they were sent
	 * Useful for PATCH request (partial entity update) - only fields sent by user are isset to prevent accidental override by default value
	 */
	public static function none(): self
	{
		return self::from(self::None);
	}

	public static function tryFrom(int $value): ?self
	{
		$key = self::ValuesAndNames[$value] ?? null;

		if ($key === null) {
			return null;
		}

		return self::$instances[$key] ??= new self($key, $value);
	}

	public static function from(int $value): self
	{
		$self = self::tryFrom($value);

		if ($self === null) {
			throw new ValueError();
		}

		return $self;
	}

	/**
	 * @return array<self>
	 */
	public static function cases(): array
	{
		$cases = [];
		foreach (self::ValuesAndNames as $value => $name) {
			$cases[] = self::from($value);
		}

		return $cases;
	}

}
