<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Processing;

use Orisai\Exceptions\Logic\InvalidState;
use Orisai\ObjectMapper\Context\SkippedPropertiesContext;
use Orisai\ObjectMapper\MappedObject;
use WeakMap;

final class SkippedPropertiesContextMap
{

	/** @var WeakMap<MappedObject, SkippedPropertiesContext|null> */
	private WeakMap $map;

	public function __construct()
	{
		$this->map = new WeakMap();
	}

	public function setSkippedPropertiesContext(MappedObject $object, ?SkippedPropertiesContext $context): void
	{
		if ($context === null) {
			$this->map->offsetUnset($object);
		} else {
			$this->map->offsetSet($object, $context);
		}
	}

	public function hasSkippedPropertiesContext(MappedObject $object): bool
	{
		return $this->map->offsetExists($object);
	}

	public function getSkippedPropertiesContext(MappedObject $object): SkippedPropertiesContext
	{
		$context = $this->map->offsetExists($object)
			? $this->map->offsetGet($object)
			: null;

		if ($context === null) {
			throw InvalidState::create()
				->withMessage('Check partial object existence with hasSkippedPropertiesContext()');
		}

		return $context;
	}

}
