<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Processing\Context;

use Orisai\ObjectMapper\MappedObject;
use Orisai\ObjectMapper\Processing\Options;
use function array_keys;

final class DynamicContext
{

	private Options $options;

	private bool $initializeObjects;

	/** @var array<class-string<MappedObject>, true> */
	private array $processedClasses = [];

	public function __construct(Options $options, bool $initializeObjects)
	{
		$this->options = $options;
		$this->initializeObjects = $initializeObjects;
	}

	/**
	 * @return static
	 */
	public function createClone(): self
	{
		$clone = clone $this;
		$clone->options = $this->options->createClone();

		return $clone;
	}

	public function getOptions(): Options
	{
		return $this->options;
	}

	public function shouldInitializeObjects(): bool
	{
		return $this->initializeObjects;
	}

	/**
	 * @param class-string<MappedObject> $class
	 * @return static
	 */
	public function withProcessedClass(string $class): self
	{
		$self = $this->createClone();
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

}
