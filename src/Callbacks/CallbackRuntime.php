<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Callbacks;

use ValueError;

final class CallbackRuntime
{

	public const
		ProcessWithoutMapping = 'processWithoutMapping',
		Process = 'process',
		Always = 'always';

	private const ValuesAndNames = [
		self::ProcessWithoutMapping => 'ProcessWithoutMapping',
		self::Process => 'Process',
		self::Always => 'Always',
	];

	/** @readonly */
	public string $name;

	/** @readonly */
	public string $value;

	/** @var array<string, self> */
	private static array $instances = [];

	private function __construct(string $name, string $value)
	{
		$this->name = $name;
		$this->value = $value;
	}

	public static function processWithoutMapping(): self
	{
		return self::from(self::ProcessWithoutMapping);
	}

	public static function process(): self
	{
		return self::from(self::Process);
	}

	public static function always(): self
	{
		return self::from(self::Always);
	}

	public static function tryFrom(string $value): ?self
	{
		$key = self::ValuesAndNames[$value] ?? null;

		if ($key === null) {
			return null;
		}

		return self::$instances[$key] ??= new self($key, $value);
	}

	public static function from(string $value): self
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
