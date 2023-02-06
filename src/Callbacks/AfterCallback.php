<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Callbacks;

use Orisai\Exceptions\Logic\InvalidArgument;
use ReflectionClass;
use ReflectionMethod;
use ReflectionParameter;
use function in_array;
use function sprintf;

final class AfterCallback extends BaseCallback
{

	protected static function validateClassMethodDataParam(
		ReflectionClass $class,
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
				$class->getName(),
				$method->getName(),
				$type ?? 'none',
			));
	}

	protected static function validateClassMethodReturn(ReflectionClass $class, ReflectionMethod $method): void
	{
		$type = self::getTypeName($method->getReturnType());

		if (in_array($type, ['array', 'void', 'never'], true)) {
			return;
		}

		throw InvalidArgument::create()
			->withMessage(sprintf(
				'Return type of class callback method %s::%s should be "array", "void" or "never" instead of %s',
				$class->getName(),
				$method->getName(),
				$type ?? 'none',
			));
	}

	protected static function validatePropertyMethodDataParam(
		ReflectionClass $class,
		ReflectionMethod $method,
		ReflectionParameter $paramData
	): void
	{
		// Any type is okay
	}

}
