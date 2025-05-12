<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Meta\Scope;

final class Target
{

	private const
		TargetClass = 'TargetClass',
		TargetConstant = 'TargetConstant',
		TargetProperty = 'TargetProperty',
		TargetMethod = 'TargetMethod',
		TargetParameter = 'TargetParameter';

	private const Names = [
		self::TargetClass,
		self::TargetConstant,
		self::TargetProperty,
		self::TargetMethod,
		self::TargetParameter,
	];

	/** @readonly */
	public string $name;

	/** @var array<string, self> */
	private static array $instances = [];

	private function __construct(string $name)
	{
		$this->name = $name;
	}

	public static function targetClass(): self
	{
		return self::from(self::TargetClass);
	}

	public static function targetConstant(): self
	{
		return self::from(self::TargetConstant);
	}

	public static function targetProperty(): self
	{
		return self::from(self::TargetProperty);
	}

	public static function targetMethod(): self
	{
		return self::from(self::TargetMethod);
	}

	public static function targetParameter(): self
	{
		return self::from(self::TargetParameter);
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
