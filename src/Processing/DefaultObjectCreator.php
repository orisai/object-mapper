<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Processing;

use Orisai\Exceptions\Logic\InvalidState;
use Orisai\Exceptions\Message;
use Orisai\ObjectMapper\Attributes\Modifiers\CreateWithoutConstructor;
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
			$skipConstructorClass = CreateWithoutConstructor::class;

			$message = Message::create()
				->withContext("Creating instance of class '$class' via $selfClass.")
				->withProblem('Class has required constructor arguments and could not be created.')
				->withSolution(
					"Use another '$creatorClass' implementation or skip constructor with '$skipConstructorClass'.",
				);

			throw InvalidState::create()
				->withMessage($message);
		}

		return new $class();
	}

}
