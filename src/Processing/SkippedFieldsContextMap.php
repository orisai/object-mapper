<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Processing;

use Orisai\Exceptions\Logic\InvalidState;
use Orisai\ObjectMapper\Context\SkippedFieldsContext;
use Orisai\ObjectMapper\MappedObject;
use WeakMap;

final class SkippedFieldsContextMap
{

	/** @var WeakMap<MappedObject, SkippedFieldsContext|null> */
	private WeakMap $map;

	public function __construct()
	{
		$this->map = new WeakMap();
	}

	public function setSkippedFieldsContext(MappedObject $object, ?SkippedFieldsContext $context): void
	{
		if ($context === null) {
			$this->map->offsetUnset($object);
		} else {
			$this->map->offsetSet($object, $context);
		}
	}

	public function hasSkippedFieldsContext(MappedObject $object): bool
	{
		return $this->map->offsetExists($object);
	}

	public function getSkippedFieldsContext(MappedObject $object): SkippedFieldsContext
	{
		$context = $this->map->offsetExists($object)
			? $this->map->offsetGet($object)
			: null;

		if ($context === null) {
			throw InvalidState::create()
				->withMessage('Check partial object existence with hasSkippedFieldsContext()');
		}

		return $context;
	}

}
