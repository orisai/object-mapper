<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Annotation;

use function array_key_exists;

trait AutoMappedAnnotation
{

	/** @var array<mixed> */
	private array $args;

	/**
	 * @param array<mixed> $values
	 */
	final public function __construct(array $values)
	{
		$mainProperty = $this->getMainProperty();

		if (array_key_exists('value', $values) && $mainProperty !== null && $mainProperty !== 'value') {
			$values[$mainProperty] = $values['value'];
			unset($values['value']);
		}

		$this->args = $this->resolveArgs($values);
	}

	/**
	 * @return array<mixed>
	 */
	final public function getArgs(): array
	{
		return $this->args;
	}

	protected function getMainProperty(): ?string
	{
		return null;
	}

	/**
	 * @param array<mixed> $args
	 * @return array<mixed>
	 */
	protected function resolveArgs(array $args): array
	{
		return $args;
	}

}
