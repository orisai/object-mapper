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
use function trait_exists;

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
			+ self::getTraits($class),
		);
	}

	/**
	 * @param class-string $class
	 * @return array<class-string>
	 */
	private static function getTraits(string $class): array
	{
		$traitsByClass = [];
		$traitsByClass[] = self::getClassUsesRecursively($class);

		$parents = class_parents($class);
		assert($parents !== false);
		foreach ($parents as $parent) {
			$traitsByClass[] = self::getClassUsesRecursively($parent);
		}

		return array_unique(array_merge(...$traitsByClass));
	}

	/**
	 * @param class-string $class
	 * @return array<class-string>
	 */
	private static function getClassUsesRecursively(string $class): array
	{
		$traits = class_uses($class);
		assert($traits !== false);

		$traitsByTrait = [];
		$traitsByTrait[] = $traits;

		foreach ($traits as $trait) {
			assert(trait_exists($trait));
			$traitsByTrait[] = self::getClassUsesRecursively($trait);
		}

		return array_merge(...$traitsByTrait);
	}

}
