<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper;

use Nette\Utils\ObjectHelpers;
use Orisai\Exceptions\Logic\InvalidState;
use Orisai\ObjectMapper\Context\PartiallyInitializedObjectContext;
use function sprintf;

/**
 * Base class required for value objects
 */
abstract class ValueObject
{

	private ?PartiallyInitializedObjectContext $partialContext = null;

	/** @var array<mixed> */
	private array $rawValues;

	public function setPartialContext(?PartiallyInitializedObjectContext $partialContext): void
	{
		$this->partialContext = $partialContext;
	}

	public function hasPartialContext(): bool
	{
		return $this->partialContext !== null;
	}

	public function getPartialContext(): PartiallyInitializedObjectContext
	{
		if ($this->partialContext === null) {
			throw InvalidState::create()
				->withMessage('Check partial object existence with hasPartialContext()');
		}

		return $this->partialContext;
	}

	/**
	 * @param array<mixed> $values
	 */
	public function setRawValues(array $values): void
	{
		$this->rawValues = $values;
	}

	/**
	 * @return array<mixed>
	 */
	public function getRawValues(): array
	{
		if (!isset($this->rawValues)) {
			throw InvalidState::create()
				->withMessage(sprintf(
					'Cannot get raw values as they were never set. You may achieve it by setting %s::setFillRawValues(true)',
					Options::class,
				));
		}

		return $this->rawValues;
	}

	public function __get(string $name): void
	{
		ObjectHelpers::strictGet(static::class, $name);
	}

	/**
	 * @param mixed $value
	 */
	public function __set(string $name, $value): void
	{
		ObjectHelpers::strictSet(static::class, $name);
	}

	public function __isset(string $name): bool
	{
		return false;
	}

	/**
	 * @param array<mixed> $arguments
	 */
	public function __call(string $name, array $arguments): void
	{
		ObjectHelpers::strictCall(static::class, $name);
	}

	/**
	 * @param array<mixed> $arguments
	 */
	public static function __callStatic(string $name, array $arguments): void
	{
		ObjectHelpers::strictStaticCall(static::class, $name);
	}

}
