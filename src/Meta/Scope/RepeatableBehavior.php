<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Meta\Scope;

final class RepeatableBehavior
{

	private const
		NoRepeat = 'NoRepeat',
		Merge = 'Merge',
		Override = 'Override';

	private const Names = [
		self::NoRepeat,
		self::Merge,
		self::Override,
	];

	/** @readonly */
	public string $name;

	/** @var array<string, self> */
	private static array $instances = [];

	private function __construct(string $name)
	{
		$this->name = $name;
	}

	public static function noRepeat(): self
	{
		return self::from(self::NoRepeat);
	}

	public static function merge(): self
	{
		return self::from(self::Merge);
	}

	public static function override(): self
	{
		return self::from(self::Override);
	}

	private static function from(string $name): self
	{
		return self::$instances[$name]
			?? (self::$instances[$name] = new self($name));
	}

	/**
	 * @return array<self>
	 */
	public static function cases(): array
	{
		$cases = [];
		foreach (self::Names as $name) {
			$cases[] = self::from($name);
		}

		return $cases;
	}

}
