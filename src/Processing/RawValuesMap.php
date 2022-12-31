<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Processing;

use Orisai\Exceptions\Logic\InvalidState;
use Orisai\ObjectMapper\MappedObject;
use WeakMap;
use function sprintf;

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
			throw InvalidState::create()
				->withMessage(sprintf(
					'Cannot get raw values as they were never set. You may achieve it by setting %s::setFillRawValues(true)',
					Options::class,
				));
		}

		return $this->map->offsetGet($object);
	}

}
