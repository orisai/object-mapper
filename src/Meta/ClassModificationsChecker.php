<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Meta;

use ReflectionClass;
use function array_merge;
use function assert;

final class ClassModificationsChecker
{

	/**
	 * @param ReflectionClass<object> $class
	 * @return list<string>
	 */
	public static function getSourceFiles(ReflectionClass $class): array
	{
		if ($class->isInternal()) {
			return [];
		}

		$filesByReflector = [];

		$parent = $class->getParentClass();
		if ($parent !== false) {
			$filesByReflector[] = self::getSourceFiles($parent);
		}

		foreach ($class->getInterfaces() as $interface) {
			$filesByReflector[] = self::getSourceFiles($interface);
		}

		foreach ($class->getTraits() as $trait) {
			$filesByReflector[] = self::getSourceFiles($trait);
		}

		$file = $class->getFileName();
		assert($file !== false); // Only internal should be false
		$filesByReflector[][] = $file;

		return array_merge(...$filesByReflector);
	}

}
