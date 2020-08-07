<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper;

use Orisai\Exceptions\Logic\InvalidState;
use function sprintf;

class Options
{

	private bool $requireDefaultValues = false;
	private bool $preFillDefaultValues = false;
	private bool $fillRawValues = false;

	/**
	 * @var array<string, array<mixed>>
	 * @phpstan-var array<class-string, array<mixed>>
	 */
	private array $dynamicContexts = [];

	/**
	 * Require default values to be sent
	 */
	final public function setRequireDefaultValues(bool $requireDefaultValues = true): void
	{
		$this->requireDefaultValues = $requireDefaultValues;
	}

	final public function isRequireDefaultValues(): bool
	{
		return $this->requireDefaultValues;
	}

	/**
	 * Fill default value if none was given
	 * Note: used only if objects are not initialized
	 * Mote: used only if default values are not required to be sent
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
	 * Make user-sent values accessible by $valueObject->getRawValues()
	 * Note: used only if objects are initialized
	 * Note: use only for debug, it may lead to significant raw data grow in bigger hierarchies
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
	 * @phpstan-param class-string $class
	 */
	final public function setDynamicContext(string $class, array $context): void
	{
		$this->dynamicContexts[$class] = $context;
	}

	/**
	 * @phpstan-param class-string $class
	 */
	final public function removeDynamicContext(string $class): void
	{
		unset($this->dynamicContexts[$class]);
	}

	/**
	 * @phpstan-param class-string $class
	 */
	final public function hasDynamicContext(string $class): bool
	{
		return isset($this->dynamicContexts[$class]);
	}

	/**
	 * @return array<mixed>
	 * @phpstan-param class-string $class
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
