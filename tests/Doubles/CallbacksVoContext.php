<?php declare(strict_types = 1);

namespace Tests\Orisai\ObjectMapper\Doubles;

use Orisai\ObjectMapper\MappedObject;

final class CallbacksVoContext
{

	/** @var class-string<MappedObject> */
	private string $dynamicStructureType;

	/**
	 * @param class-string<MappedObject> $dynamicStructureType
	 */
	public function __construct(string $dynamicStructureType)
	{
		$this->dynamicStructureType = $dynamicStructureType;
	}

	/**
	 * @return class-string<MappedObject>
	 */
	public function getDynamicStructureType(): string
	{
		return $this->dynamicStructureType;
	}

}
