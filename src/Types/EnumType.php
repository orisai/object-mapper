<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Types;

final class EnumType implements Type
{

	/** @var array<mixed> */
	private array $cases;

	/**
	 * @param array<mixed> $cases
	 */
	public function __construct(array $cases)
	{
		$this->cases = $cases;
	}

	/**
	 * @return array<mixed>
	 */
	public function getCases(): array
	{
		return $this->cases;
	}

}
