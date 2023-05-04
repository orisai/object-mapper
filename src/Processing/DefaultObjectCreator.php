<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Processing;

use Orisai\Exceptions\Logic\InvalidState;
use Orisai\Exceptions\Message;
use Orisai\ObjectMapper\MappedObject;
use Orisai\ObjectMapper\Modifiers\CreateWithoutConstructor;
use ReflectionClass;

final class DefaultObjectCreator implements ObjectCreator
{

	public function createInstance(string $class, bool $useConstructor): MappedObject
	{
		if (!$useConstructor) {
			return (new ReflectionClass($class))->newInstanceWithoutConstructor();
		}

		return new $class();
	}

	public function checkClassIsInstantiable(string $class, bool $useConstructor): void
	{
		$reflection = new ReflectionClass($class);

		if (!$useConstructor) {
			$reflection->newInstanceWithoutConstructor();

			return;
		}

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

		new $class();
	}

}
