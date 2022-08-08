<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Printers;

use Orisai\ObjectMapper\Types\TypeParameter;

/**
 * @template T of string|array
 */
interface TypeToPrimitiveConverter
{

	/**
	 * @return T
	 */
	public function printMessage(string $message);

	/**
	 * @param array<int|string, TypeParameter> $parameters
	 * @return T
	 */
	public function printSimpleValue(string $name, array $parameters);

	/**
	 * @param array<int|string, mixed> $values
	 * @return T
	 */
	public function printEnum(array $values);

	/**
	 * @param array<int|string, TypeParameter> $parameters
	 * @return T
	 */
	public function printParameters(array $parameters);

	/**
	 * @param array<int|string, T> $subtypes
	 * @return T
	 */
	public function printCompound(string $operator, array $subtypes);

	/**
	 * @param T|null                                   $keyType
	 * @param T|null                                   $itemType
	 * @param array<int|string, TypeParameter>         $parameters
	 * @param array<int|string, array{T|null, T|null}> $invalidPairs
	 * @return T
	 */
	public function printArray(
		string $name,
		array $parameters,
		$keyType,
		$itemType,
		array $invalidPairs = []
	);

	/**
	 * @param array<int|string, T> $fields
	 * @param array<int|string, T> $errors
	 * @return T
	 */
	public function printShape(array $fields, array $errors = []);

	/**
	 * @param array<int, string>   $pathNodes
	 * @param array<int|string, T> $fields
	 * @param array<int|string, T> $errors
	 * @return T
	 */
	public function printError(array $pathNodes, array $fields, array $errors);

}
