<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper;

use Nette\Utils\ObjectHelpers;
use Orisai\Exceptions\Logic\InvalidState;
use Orisai\ObjectMapper\Context\SkippedPropertiesContext;
use ReflectionException;
use ReflectionProperty;
use function sprintf;

/**
 * Base class required for value objects
 */
abstract class ValueObject
{

	private ?SkippedPropertiesContext $skippedPropertiesContext = null;

	/** @var array<mixed> */
	private array $rawValues;

	public function setSkippedPropertiesContext(?SkippedPropertiesContext $partialContext): void
	{
		$this->skippedPropertiesContext = $partialContext;
	}

	public function hasSkippedPropertiesContext(): bool
	{
		return $this->skippedPropertiesContext !== null;
	}

	public function getSkippedPropertiesContext(): SkippedPropertiesContext
	{
		if ($this->skippedPropertiesContext === null) {
			throw InvalidState::create()
				->withMessage('Check partial object existence with hasSkippedPropertiesContext()');
		}

		return $this->skippedPropertiesContext;
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

	public function isInitialized(string $property): bool
	{
		return (new ReflectionProperty($this, $property))->isInitialized($this);
	}

	/**
	 * Checks if the public non-static property exists.
	 */
	private static function hasProperty(string $class, string $name): bool
	{
		static $cache;
		$prop = &$cache[$class][$name];

		if ($prop !== null) {
			return $prop;
		}

		try {
			$rp = new ReflectionProperty($class, $name);
			if ($rp->isPublic() && !$rp->isStatic()) {
				return $prop = true;
			}
		} catch (ReflectionException $e) {
			// If it failed than it does not have that property
		}

		return $prop = false;
	}

	/**
	 * @return never
	 */
	final public function __get(string $name): void
	{
		ObjectHelpers::strictGet(static::class, $name);
	}

	/**
	 * @param mixed $value
	 */
	final public function __set(string $name, $value): void
	{
		$class = static::class;

		if (static::hasProperty($class, $name)) {
			$this->$name = $value;
		} else {
			ObjectHelpers::strictSet($class, $name);
		}
	}

	final public function __isset(string $name): bool
	{
		return false;
	}

	/**
	 * @param array<mixed> $arguments
	 * @return never
	 */
	final public function __call(string $name, array $arguments): void
	{
		ObjectHelpers::strictCall(static::class, $name);
	}

	/**
	 * @param array<mixed> $arguments
	 * @return never
	 */
	final public static function __callStatic(string $name, array $arguments): void
	{
		ObjectHelpers::strictStaticCall(static::class, $name);
	}

}
