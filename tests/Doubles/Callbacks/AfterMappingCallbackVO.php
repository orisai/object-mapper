<?php declare(strict_types = 1);

namespace Tests\Orisai\ObjectMapper\Doubles\Callbacks;

use Orisai\ObjectMapper\Callbacks\AfterMapping;
use Orisai\ObjectMapper\MappedObject;
use Orisai\ObjectMapper\Rules\IntValue;
use Orisai\ObjectMapper\Rules\StringValue;

/**
 * @AfterMapping("afterMapping")
 * @AfterMapping("afterMapping2")
 */
final class AfterMappingCallbackVO implements MappedObject
{

	/** @StringValue() */
	public string $string;

	/** @IntValue() */
	public int $int;

	/** @var array<mixed> */
	public array $mappedValues;

	/** @var array<mixed> */
	public array $mappedValues2;

	public function afterMapping(): void
	{
		$this->mappedValues = [
			'string' => $this->string,
			'int' => $this->int,
		];
	}

	public function afterMapping2(): void
	{
		$this->mappedValues2 = [
			'string' => $this->string,
			'int' => $this->int,
		];
	}

}
