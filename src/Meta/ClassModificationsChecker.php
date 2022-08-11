<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Meta;

use ReflectionClass;
use function array_keys;
use function array_merge;
use function array_unique;
use function assert;
use function class_implements;
use function class_parents;
use function class_uses;
use function get_parent_class;

final class ClassModificationsChecker
{

	/**
	 * @param class-string $class
	 * @return array<string>
	 */
	public static function getSourceFiles(string $class): array
	{
		$files = [];
		foreach (self::getAllTypes($class) as $type) {
			$fileName = (new ReflectionClass($type))->getFileName();
			if ($fileName === false) { // is internal
				continue;
			}

			$files[] = $fileName;
		}

		return $files;
	}

	/**
	 * @param class-string $class
	 * @return array<class-string>
	 */
	private static function getAllTypes(string $class): array
	{
		$parents = class_parents($class);
		assert($parents !== false);

		$implements = class_implements($class);
		assert($implements !== false);

		return array_keys(
			[$class => null]
			+ $parents
			+ $implements
			+ self::getUsedTraits($class),
		);
	}

	/**
	 * @param class-string $class
	 * @return array<string>
	 */
	private static function getUsedTraits(string $class): array
	{
		$traits = [];

		do {
			$uses = class_uses($class);
			assert($uses !== false);
			$traits = array_merge($uses, $traits);
		} while ($class = get_parent_class($class));

		foreach ($traits as $trait => $same) {
			$uses = class_uses($trait);
			assert($uses !== false);
			$traits = array_merge($uses, $traits);
		}

		return array_unique($traits);
	}

}
