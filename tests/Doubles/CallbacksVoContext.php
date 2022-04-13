<?php declare(strict_types = 1);

namespace Tests\Orisai\ObjectMapper\Doubles;

use Orisai\ObjectMapper\MappedObject;

final class CallbacksVoContext
{

	/** @var class-string<MappedObject> */
	private string $objectType;

	/**
	 * @param class-string<MappedObject> $objectType
	 */
	public function __construct(string $objectType)
	{
		$this->objectType = $objectType;
	}

	/**
	 * @return class-string<MappedObject>
	 */
	public function getObjectType(): string
	{
		return $this->objectType;
	}

}
