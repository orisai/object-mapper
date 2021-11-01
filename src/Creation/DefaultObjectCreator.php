<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Creation;

use Orisai\Exceptions\Logic\InvalidState;
use Orisai\ObjectMapper\ValueObject;
use ReflectionClass;

final class DefaultObjectCreator implements ObjectCreator
{

	public function createInstance(string $class): ValueObject
	{
		$reflection = new ReflectionClass($class);

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
