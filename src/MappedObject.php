<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper;

use Nette\Utils\ObjectHelpers;
use Orisai\Exceptions\Logic\InvalidState;
use Orisai\ObjectMapper\Context\SkippedPropertiesContext;
use Orisai\ObjectMapper\Processing\Options;
use ReflectionException;
use ReflectionProperty;
use function sprintf;

/**
 * Base class required for mapped objects
 */
abstract class MappedObject
{

	private ?SkippedPropertiesContext $skippedPropertiesContext = null;

	private bool $hasRawValues = false;

	/** @var mixed */
	private $rawValues;

	public function setSkippedPropertiesContext(?SkippedPropertiesContext $context): void
	{
		$this->skippedPropertiesContext = $context;
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
	 * @param mixed $values
	 */
	public function setRawValues($values): void
	{
		$this->hasRawValues = true;
		$this->rawValues = $values;
	}

	/**
	 * @return mixed
	 */
	public function getRawValues()
	{
		if (!$this->hasRawValues) {
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
			$ref = new ReflectionProperty($class, $name);
		} catch (ReflectionException $e) {
			return $prop = false;
		}

		return $prop = ($ref->isPublic() && !$ref->isStatic());
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

		if (self::hasProperty($class, $name)) {
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
