<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper;

use Nette\Utils\ObjectHelpers;
use Orisai\Exceptions\Logic\InvalidState;
use Orisai\Exceptions\Logic\MemberInaccessible;
use Orisai\ObjectMapper\Context\SkippedPropertiesContext;
use ReflectionClass;
use ReflectionProperty;
use function array_filter;
use function array_key_exists;
use function property_exists;
use function sprintf;

/**
 * Base class required for value objects
 */
abstract class ValueObject
{

	private ?SkippedPropertiesContext $skippedPropertiesContext = null;

	/** @var array<mixed> */
	private array $processedValues = [];

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

	private function getPropertyHint(string $propertyName): ?string
	{
		$ref = new ReflectionClass(static::class);
		$propertyNames = array_filter($ref->getProperties(ReflectionProperty::IS_PUBLIC), static fn (ReflectionProperty $p) => !$p->isStatic());

		return ObjectHelpers::getSuggestion($propertyNames, $propertyName);
	}

	/**
	 * @return mixed
	 */
	final public function __get(string $name)
	{
		if (!array_key_exists($name, $this->processedValues)) {
			$hint = $this->getPropertyHint($name);

			throw MemberInaccessible::create()
				->withMessage(sprintf(
					'Cannot read an undeclared property `%s::%s`%s',
					static::class,
					$name,
					$hint !== null ? sprintf(', did you mean "%s"?', $hint) : '',
				));
		}

		return $this->processedValues[$name];
	}

	/**
	 * @param mixed $value
	 */
	final public function __set(string $name, $value): void
	{
		if (!property_exists(static::class, $name)) {
			$hint = $this->getPropertyHint($name);

			throw MemberInaccessible::create()
				->withMessage(sprintf(
					'Cannot write to an undeclared property `%s::%s`%s',
					static::class,
					$name,
					$hint !== null ? sprintf(', did you mean "%s"?', $hint) : '',
				));
		}

		$this->processedValues[$name] = $value;
	}

	final public function __isset(string $name): bool
	{
		return array_key_exists($name, $this->processedValues);
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
