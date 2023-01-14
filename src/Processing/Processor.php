<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Processing;

use Orisai\ObjectMapper\Exception\InvalidData;
use Orisai\ObjectMapper\MappedObject;

interface Processor
{

	/**
	 * Validate data against $class schema, call all callbacks and map data to initialized $class instance
	 *
	 * @template T of MappedObject
	 * @param mixed $data
	 * @param class-string<T> $class
	 * @return T
	 * @throws InvalidData
	 */
	public function process($data, string $class, ?Options $options = null): MappedObject;

	/**
	 * Validate data against $class schema and call before/after processing callbacks
	 *
	 * @param mixed                      $data
	 * @param class-string<MappedObject> $class
	 * @return array<int|string, mixed>
	 * @throws InvalidData
	 */
	public function processWithoutMapping($data, string $class, ?Options $options = null): array;

	/**
	 * Validate and initialize MappedObject fields which were skipped due to Skipped modifier
	 *
	 * @param list<string> $fields
	 * @throws InvalidData
	 */
	public function processSkippedFields(
		array $fields,
		MappedObject $object,
		?Options $options = null
	): void;

}
