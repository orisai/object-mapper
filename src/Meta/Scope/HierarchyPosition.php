<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Meta\Scope;

final class HierarchyPosition
{

	private const
		Anywhere = 'Anywhere',
		FirstType = 'FirstType';

	private const Names = [
		self::Anywhere,
		self::FirstType,
	];

	/** @readonly */
	public string $name;

	/** @var array<string, self> */
	private static array $instances = [];

	private function __construct(string $name)
	{
		$this->name = $name;
	}

	public static function firstType(): self
	{
		return self::from(self::FirstType);
	}

	public static function anywhere(): self
	{
		return self::from(self::Anywhere);
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
