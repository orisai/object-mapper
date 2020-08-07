<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Meta;

use ReflectionClass;
use function array_keys;
use function array_map;
use function array_merge;
use function array_unique;
use function class_implements;
use function class_parents;
use function class_uses;
use function filemtime;
use function get_parent_class;
use function md5;
use function serialize;

final class ClassModificationsChecker
{

	public static function getLastModificationTimeHash(string $class): string
	{
		$modificationTimes = array_map(static fn (string $type): int => filemtime((new ReflectionClass($type))->getFileName()), self::getAllTypes($class));

		return md5(serialize($modificationTimes));
	}

	/**
	 * @return array<string>
	 */
	public static function getSourceFiles(string $class): array
	{
		return array_map(static fn (string $type): string => (new ReflectionClass($type))->getFileName(), self::getAllTypes($class));
	}

	/**
	 * @return array<string>
	 */
	private static function getAllTypes(string $class): array
	{
		return array_keys(
			[$class => null]
			+ class_parents($class)
			+ class_implements($class)
			+ self::getUsedTraits($class),
		);
	}

	/**
	 * @return array<string>
	 */
	private static function getUsedTraits(string $class): array
	{
		$traits = [];

		do {
			$traits = array_merge(class_uses($class), $traits);
		} while ($class = get_parent_class($class));

		foreach ($traits as $trait => $same) {
			$traits = array_merge(class_uses($trait), $traits);
		}

		return array_unique($traits);
	}

}
