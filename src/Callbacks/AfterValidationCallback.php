<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Callbacks;

use Orisai\Exceptions\Logic\InvalidArgument;
use ReflectionMethod;
use ReflectionParameter;
use function in_array;
use function sprintf;

final class AfterValidationCallback extends ValidationCallback
{

	protected static function validateClassMethodDataParam(
		ReflectionMethod $method,
		ReflectionParameter $paramData
	): void
	{
		$type = self::getTypeName($paramData->getType());

		if ($type === 'array') {
			return;
		}

		throw InvalidArgument::create()
			->withMessage(sprintf(
				'First parameter of class callback method %s::%s should have "array" type instead of %s',
				$method->getDeclaringClass()->getName(),
				$method->getName(),
				$type ?? 'none',
			));
	}

	protected static function validateClassMethodReturn(ReflectionMethod $method): void
	{
		$type = self::getTypeName($method->getReturnType());

		if (in_array($type, ['array', 'void', 'never'], true)) {
			return;
		}

		throw InvalidArgument::create()
			->withMessage(sprintf(
				'Return type of class callback method %s::%s should be "array", "void" or "never" instead of %s',
				$method->getDeclaringClass()->getName(),
				$method->getName(),
				$type ?? 'none',
			));
	}

	protected static function validatePropertyMethodDataParam(
		ReflectionMethod $method,
		ReflectionParameter $paramData
	): void
	{
		// Any type is okay
	}

}
