<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper;

use Orisai\Exceptions\Logic\InvalidState;
use function sprintf;

class Options
{

	public const REQUIRE_NON_DEFAULT = 1;
	public const REQUIRE_ALL = 2;
	public const REQUIRE_NONE = 3;

	/** @phpstan-var self::REQUIRE_* */
	private int $requiredFields = self::REQUIRE_NON_DEFAULT;
	private bool $preFillDefaultValues = false;
	private bool $fillRawValues = false;

	/** @var array<class-string, array<mixed>> */
	private array $dynamicContexts = [];

	/**
	 * REQUIRE_NON_DEFAULT
	 * 		- default option, only fields without default value are required
	 * REQUIRE_ALL
	 * 		- all fields are required
	 * 		- defaults are used only by rules which merge them
	 * 		- useful for PUT request (full entity replace) - user must sent all fields to prevent accidental override by default value
	 * REQUIRE_NONE
	 * 		- no fields are required
	 * 		- fields which are not sent are unset so isset could be used to check if they were sent
	 * 		- useful for PATCH request (partial entity update) - only fields sent by user are isset to prevent accidental override by default value
	 *
	 * @phpstan-param self::REQUIRE_* $fields
	 */
	final public function setRequiredFields(int $fields): void
	{
		$this->requiredFields = $fields;
	}

	/**
	 * @phpstan-return self::REQUIRE_*
	 */
	final public function getRequiredFields(): int
	{
		return $this->requiredFields;
	}

	/**
	 * Add default field value to returned array if none was given
	 * Note: affect behavior only if objects are not initialized (array is returned, not VO)
	 * Mote: used only if default values are not required to be sent (REQUIRE_ALL)
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
	 * @return array<mixed>
	 * @param class-string $class
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
