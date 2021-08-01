<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Processing;

use Orisai\ObjectMapper\Exception\InvalidData;
use Orisai\ObjectMapper\Options;
use Orisai\ObjectMapper\ValueObject;

interface Processor
{

	/**
	 * Validate data against $class schema, call all callbacks and map data to initialized $class instance
	 *
	 * @template T of ValueObject
	 * @param mixed $data
	 * @param class-string<T> $class
	 * @return T
	 * @throws InvalidData
	 */
	public function process($data, string $class, ?Options $options = null): ValueObject;

	/**
	 * Validate data against $class schema and call before/after processing callbacks
	 *
	 * @param mixed $data
	 * @param class-string<ValueObject> $class
	 * @return array<int|string, mixed>
	 * @throws InvalidData
	 */
	public function processWithoutInitialization($data, string $class, ?Options $options = null): array;

	/**
	 * Validate and initialize ValueObject properties which were skipped due to LateProcessed modifier
	 *
	 * @param array<string> $properties
	 * @throws InvalidData
	 */
	public function processSkippedProperties(
		array $properties,
		ValueObject $object,
		?Options $options = null
	): void;

}
