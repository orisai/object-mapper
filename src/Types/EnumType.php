<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Types;

final class EnumType implements Type
{

	/** @var array<mixed> */
	private array $values;

	/**
	 * @param array<mixed> $values
	 */
	public function __construct(array $values)
	{
		$this->values = $values;
	}

	/**
	 * @return array<mixed>
	 */
	public function getValues(): array
	{
		return $this->values;
	}

}
