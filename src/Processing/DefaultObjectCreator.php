<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Processing;

use Orisai\Exceptions\Logic\InvalidState;
use Orisai\ObjectMapper\MappedObject;
use ReflectionClass;

final class DefaultObjectCreator implements ObjectCreator
{

	public function createInstance(string $class, bool $useConstructor): MappedObject
	{
		$reflection = new ReflectionClass($class);

		if (!$useConstructor) {
			return $reflection->newInstanceWithoutConstructor();
		}

		// TODO - tohle by mohlo být jen při načítání metadat, není třeba pro runtime
		$ctor = $reflection->getConstructor();
		if ($ctor !== null && $ctor->getNumberOfRequiredParameters() !== 0) {
			$selfClass = self::class;
			$creatorClass = ObjectCreator::class;

			throw InvalidState::create()
				->withMessage("$selfClass is unable to create object with required constructor arguments. " .
					"You may want use some other $creatorClass implementation.");
		}

		return new $class();
	}

}
