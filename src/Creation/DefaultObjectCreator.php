<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Creation;

use ArgumentCountError;
use Orisai\Exceptions\Logic\InvalidState;
use Orisai\ObjectMapper\ValueObject;
use function sprintf;

final class DefaultObjectCreator implements ObjectCreator
{

	public function createInstance(string $class): ValueObject
	{
		try {
			$object = new $class();
		} catch (ArgumentCountError $error) {
			throw InvalidState::create()
				->withMessage(sprintf(
					'%s is unable to create object with required constructor arguments. You may want use some other %s implementation.',
					self::class,
					ObjectCreator::class,
				));
		}

		return $object;
	}

}
