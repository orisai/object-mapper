<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Callbacks;

use Orisai\Exceptions\Logic\InvalidArgument;
use ReflectionMethod;
use ReflectionParameter;
use function in_array;
use function sprintf;

final class BeforeValidationCallback extends ValidationCallback
{

	protected static function validateClassMethodDataParam(
		ReflectionMethod $method,
		ReflectionParameter $paramData
	): void
	{
		$type = self::getTypeName($paramData->getType());

		if (in_array($type, ['mixed', null], true)) {
			return;
		}

		throw InvalidArgument::create()
			->withMessage(sprintf(
				'First parameter of class callback method %s::%s should have "mixed" or none type instead of %s',
				$method->getDeclaringClass()->getName(),
				$method->getName(),
				$type,
			));
	}

	protected static function validateClassMethodReturn(ReflectionMethod $method): void
	{
		// Any type is okay
	}

	protected static function validatePropertyMethodDataParam(
		ReflectionMethod $method,
		ReflectionParameter $paramData
	): void
	{
		$type = self::getTypeName($paramData->getType());

		if (in_array($type, [null, 'mixed'], true)) {
			return;
		}

		throw InvalidArgument::create()
			->withMessage(sprintf(
				'First parameter of before field callback method %s::%s should have none or "mixed" type instead of %s',
				$method->getDeclaringClass()->getName(),
				$method->getName(),
				$type,
			));
	}

}
