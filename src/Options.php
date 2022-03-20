<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper;

use Orisai\Exceptions\Logic\InvalidState;
use Orisai\ObjectMapper\Processing\RequiredFields;
use function sprintf;

class Options
{

	private RequiredFields $requiredFields;

	private bool $preFillDefaultValues = false;

	private bool $fillRawValues = false;

	/** @var array<class-string, array<mixed>> */
	private array $dynamicContexts = [];

	public function __construct()
	{
		$this->requiredFields = RequiredFields::nonDefault();
	}

	final public function setRequiredFields(RequiredFields $fields): void
	{
		$this->requiredFields = $fields;
	}

	final public function getRequiredFields(): RequiredFields
	{
		return $this->requiredFields;
	}

	/**
	 * Add default field value to returned array if none was given
	 * Used only if objects are not initialized (array is returned, not VO)
	 * Used only if default values are not required to be sent (by RequiredFields::all())
	 */
	final public function setPreFillDefaultValues(bool $preFillDefaultValues = true): void
	{
		$this->preFillDefaultValues = $preFillDefaultValues;
	}

	final public function isPreFillDefaultValues(): bool
	{
		return $this->preFillDefaultValues;
	}

	/**
	 * Make user-sent values accessible by $mappedObject->getRawValues()
	 * Used only if objects are initialized
	 * Use only for debug, it may lead to significant raw data grow in bigger hierarchies
	 * 		 you can set data to a custom property in before class callback, if are always needed
	 */
	final public function setFillRawValues(bool $fillRawValues = true): void
	{
		$this->fillRawValues = $fillRawValues;
	}

	final public function isFillRawValues(): bool
	{
		return $this->fillRawValues;
	}

	/**
	 * @param array<mixed> $context
	 * @param class-string $class
	 */
	final public function setDynamicContext(string $class, array $context): void
	{
		$this->dynamicContexts[$class] = $context;
	}

	/**
	 * @param class-string $class
	 */
	final public function removeDynamicContext(string $class): void
	{
		unset($this->dynamicContexts[$class]);
	}

	/**
	 * @param class-string $class
	 */
	final public function hasDynamicContext(string $class): bool
	{
		return isset($this->dynamicContexts[$class]);
	}

	/**
	 * @param class-string $class
	 * @return array<mixed>
	 */
	final public function getDynamicContext(string $class): array
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

}
