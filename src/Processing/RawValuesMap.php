<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Processing;

use Orisai\Exceptions\Logic\InvalidState;
use Orisai\Exceptions\Message;
use Orisai\ObjectMapper\MappedObject;
use WeakMap;
use function get_class;

final class RawValuesMap
{

	/** @var WeakMap<MappedObject, mixed> */
	private WeakMap $map;

	public function __construct()
	{
		$this->map = new WeakMap();
	}

	/**
	 * @param mixed $values
	 */
	public function setRawValues(MappedObject $object, $values): void
	{
		$this->map->offsetSet($object, $values);
	}

	/**
	 * @return mixed
	 */
	public function getRawValues(MappedObject $object)
	{
		if (!$this->map->offsetExists($object)) {
			$objectClass = get_class($object);
			$optionsClass = Options::class;
			$message = Message::create()
				->withContext("Getting raw values for object of type '$objectClass'.")
				->withProblem('Raw values are not set.')
				->withSolution("Ensure '$optionsClass::setTrackRawValues()' is enabled and that object was"
					. ' processed in current request by object mapper (raw values are available only as long as reference to object exists).');

			throw InvalidState::create()
				->withMessage($message);
		}

		return $this->map->offsetGet($object);
	}

}
