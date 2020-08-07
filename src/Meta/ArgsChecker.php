<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Meta;

use Nette\Utils\ObjectHelpers;
use Orisai\Exceptions\Logic\InvalidArgument;
use function array_key_exists;
use function array_keys;
use function gettype;
use function implode;
use function in_array;
use function is_array;
use function is_bool;
use function is_float;
use function is_int;
use function is_string;
use function sprintf;

final class ArgsChecker
{

	/** @var array<mixed> */
	private array $args;

	private string $class;

	/**
	 * @param class-string $class
	 * @param array<mixed> $args
	 */
	public function __construct(array $args, string $class)
	{
		$this->args = $args;
		$this->class = $class;
	}

	/**
	 * @param array<string> $argNames
	 */
	public function checkAllowedArgs(array $argNames): void
	{
		$actualArgNames = array_keys($this->args);

		foreach ($actualArgNames as $name) {
			if (!in_array($name, $argNames, true)) {
				$hint = ObjectHelpers::getSuggestion($argNames, $name);

				throw InvalidArgument::create()
					->withMessage(sprintf(
						'Unknown argument "%s" given to "%s"%s',
						$name,
						$this->class,
						$hint !== null ? sprintf(', did you mean "%s"?', $hint) : '',
					));
			}
		}
	}

	public function checkNoArgs(): void
	{
		if ($this->args !== []) {
			throw InvalidArgument::create()
				->withMessage(sprintf(
					'"%s" does not accept any arguments, "%s" given',
					$this->class,
					implode(', ', array_keys($this->args)),
				));
		}
	}

	public function checkRequiredArg(string $argName): void
	{
		if (!array_key_exists($argName, $this->args)) {
			throw InvalidArgument::create()
				->withMessage(sprintf(
					'Required argument "%s" not given to "%s"',
					$argName,
					$this->class,
				));
		}
	}

	/**
	 * @param array<mixed> $values
	 */
	public function checkEnum(string $argName, array $values): void
	{
		if (!in_array($this->args[$argName], $values, true)) {
			throw InvalidArgument::create()
				->withMessage(sprintf(
					'Argument "%s" given to "%s" expects value to be one of "%s"',
					$argName,
					$this->class,
					implode(', ', $values),
				));
		}
	}

	public function hasArg(string $argName): bool
	{
		return array_key_exists($argName, $this->args);
	}

	public function checkBool(string $argName): bool
	{
		$argValue = $this->args[$argName];

		if (!is_bool($argValue)) {
			throw InvalidArgument::create()
				->withMessage($this->formatMessage('bool', $argName, $argValue));
		}

		return $argValue;
	}

	public function checkInt(string $argName): int
	{
		$argValue = $this->args[$argName];

		if (!is_int($argValue)) {
			throw InvalidArgument::create()
				->withMessage($this->formatMessage('int', $argName, $argValue));
		}

		return $argValue;
	}

	public function checkNullableInt(string $argName): ?int
	{
		$argValue = $this->args[$argName];

		if ($argValue !== null && !is_int($argValue)) {
			throw InvalidArgument::create()
				->withMessage($this->formatMessage('int|null', $argName, $argValue));
		}

		return $argValue;
	}

	public function checkFloat(string $argName): float
	{
		$argValue = $this->args[$argName];

		if (is_int($argValue)) {
			$argValue = (float) $argValue;
		}

		if (!is_float($argValue)) {
			throw InvalidArgument::create()
				->withMessage($this->formatMessage('float', $argName, $argValue));
		}

		return $argValue;
	}

	public function checkNullableFloat(string $argName): ?float
	{
		$argValue = $this->args[$argName];

		if (is_int($argValue)) {
			$argValue = (float) $argValue;
		}

		if ($argValue !== null && !is_float($argValue)) {
			throw InvalidArgument::create()
				->withMessage($this->formatMessage('float|null', $argName, $argValue));
		}

		return $argValue;
	}

	public function checkString(string $argName): string
	{
		$argValue = $this->args[$argName];

		if (!is_string($argValue)) {
			throw InvalidArgument::create()
				->withMessage($this->formatMessage('string', $argName, $argValue));
		}

		return $argValue;
	}

	public function checkNullableString(string $argName): ?string
	{
		$argValue = $this->args[$argName];

		if ($argValue !== null && !is_string($argValue)) {
			throw InvalidArgument::create()
				->withMessage($this->formatMessage('string|null', $argName, $argValue));
		}

		return $argValue;
	}

	/**
	 * @return array<mixed>
	 */
	public function checkArray(string $argName): array
	{
		$argValue = $this->args[$argName];

		if (!is_array($argValue)) {
			throw InvalidArgument::create()
				->withMessage($this->formatMessage('array', $argName, $argValue));
		}

		return $argValue;
	}

	/**
	 * @return array<mixed>|null
	 */
	public function checkNullableArray(string $argName): ?array
	{
		$argValue = $this->args[$argName];

		if ($argValue !== null && !is_array($argValue)) {
			throw InvalidArgument::create()
				->withMessage($this->formatMessage('array|null', $argName, $argValue));
		}

		return $argValue;
	}

	/**
	 * @param mixed $argValue
	 */
	public function formatMessage(string $type, string $argName, $argValue): string
	{
		return sprintf(
			'Argument "%s" given to "%s" expected to be "%s", "%s" given.',
			$argName,
			$this->class,
			$type,
			gettype($argValue),
		);
	}

}
