<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Meta;

use ReflectionClass;
use function array_merge;
use function assert;

final class ClassModificationsChecker
{

	/**
	 * @param class-string $class
	 * @return array<string>
	 */
	public static function getSourceFiles(string $class): array
	{
		return self::get(new ReflectionClass($class));
	}

	/**
	 * @param ReflectionClass<object> $class
	 * @return list<string>
	 */
	private static function get(ReflectionClass $class): array
	{
		if ($class->isInternal()) {
			return [];
		}

		$filesByReflector = [];

		$parent = $class->getParentClass();
		if ($parent !== false) {
			$filesByReflector[] = self::get($parent);
		}

		foreach ($class->getInterfaces() as $interface) {
			$filesByReflector[] = self::get($interface);
		}

		foreach ($class->getTraits() as $trait) {
			$filesByReflector[] = self::get($trait);
		}

		$file = $class->getFileName();
		assert($file !== false); // Only internal should be false
		$filesByReflector[][] = $file;

		return array_merge(...$filesByReflector);
	}

}
