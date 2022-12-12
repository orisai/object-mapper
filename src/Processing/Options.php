<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Processing;

use Orisai\Exceptions\Logic\InvalidState;
use Orisai\ObjectMapper\MappedObject;
use function array_keys;
use function get_class;
use function sprintf;

final class Options
{

	private RequiredFields $requiredFields;

	private bool $allowUnknownFields = false;

	private bool $prefillDefaultValues = false;

	private bool $fillRawValues = false;

	/** @var array<class-string, object> */
	private array $dynamicContexts = [];

	/** @var array<class-string<MappedObject>, true> */
	private array $processedClasses = [];

	public function __construct()
	{
		$this->requiredFields = RequiredFields::nonDefault();
	}

	public function setRequiredFields(RequiredFields $fields): void
	{
		$this->requiredFields = $fields;
	}

	public function getRequiredFields(): RequiredFields
	{
		return $this->requiredFields;
	}

	public function isAllowUnknownFields(): bool
	{
		return $this->allowUnknownFields;
	}

	/**
	 * Do not treat unknown properties in input value as errors if object is valid anyway
	 */
	public function setAllowUnknownFields(bool $allow = true): void
	{
		$this->allowUnknownFields = $allow;
	}

	/**
	 * Add default field value to returned array if none was given
	 * Used only if objects are not initialized (array is returned, not VO)
	 * Used only if default values are not required to be sent (by RequiredFields::all())
	 */
	public function setPrefillDefaultValues(bool $prefill = true): void
	{
		$this->prefillDefaultValues = $prefill;
	}

	public function isPrefillDefaultValues(): bool
	{
		return $this->prefillDefaultValues;
	}

	/**
	 * Make user-sent values accessible by $mappedObject->getRawValues()
	 * Used only if objects are initialized
	 * Use only for debug, it may lead to significant raw data grow in bigger hierarchies
	 * 		 you can set data to a custom property in before class callback, if are always needed
	 */
	public function setFillRawValues(bool $fill = true): void
	{
		$this->fillRawValues = $fill;
	}

	public function isFillRawValues(): bool
	{
		return $this->fillRawValues;
	}

	public function addDynamicContext(object $context): void
	{
		$this->dynamicContexts[get_class($context)] = $context;
	}

	/**
	 * @param class-string $class
	 */
	public function hasDynamicContext(string $class): bool
	{
		return isset($this->dynamicContexts[$class]);
	}

	/**
	 * @template T of object
	 * @param class-string<T> $class
	 * @return T
	 */
	public function getDynamicContext(string $class): object
	{
		if (!$this->hasDynamicContext($class)) {
			throw InvalidState::create()
				->withMessage(sprintf(
					'Class %s does not have dynamic context, check it with hasDynamicContext() or ensure it is set with setDynamicContext()',
					$class,
				));
		}

		return $this->dynamicContexts[$class];
	}

	/**
	 * @param class-string<MappedObject> $class
	 */
	public function withProcessedClass(string $class): self
	{
		$self = clone $this;
		$self->processedClasses[$class] = true;

		return $self;
	}

	/**
	 * @return list<class-string<MappedObject>>
	 */
	public function getProcessedClasses(): array
	{
		return array_keys($this->processedClasses);
	}

	/**
	 * @return static
	 */
	public function createClone(): self
	{
		return clone $this;
	}

}
