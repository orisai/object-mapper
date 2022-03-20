<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Processing;

use ValueError;
use function array_key_exists;

final class RequiredFields
{

	private const
		NON_DEFAULT = 1,
		ALL = 2,
		NONE = 3;

	private const VALUES_AND_NAMES = [
		self::NON_DEFAULT => 'nonDefault',
		self::ALL => 'all',
		self::NONE => 'none',
	];

	/** @readonly */
	public string $name;

	/** @readonly */
	public int $value;

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
		return self::from(self::NON_DEFAULT);
	}

	/**
	 * All fields are required
	 * Defaults are used only by rules which merge them
	 * Useful for PUT request (full entity replace) - user must send all fields to prevent accidental override by default value
	 */
	public static function all(): self
	{
		return self::from(self::ALL);
	}

	/**
	 * No fields are required
	 * Fields which are not sent are unset so isset could be used to check if they were sent
	 * Useful for PATCH request (partial entity update) - only fields sent by user are isset to prevent accidental override by default value
	 */
	public static function none(): self
	{
		return self::from(self::NONE);
	}

	public static function tryFrom(int $value): ?self
	{
		if (!array_key_exists($value, self::VALUES_AND_NAMES)) {
			return null;
		}

		return new self(self::VALUES_AND_NAMES[$value], $value);
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
		foreach (self::VALUES_AND_NAMES as $value => $name) {
			$cases[] = self::from($value);
		}

		return $cases;
	}

}
