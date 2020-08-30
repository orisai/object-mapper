<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Types;

final class SimpleValueType extends ParametrizedType
{

	private string $type;

	public function __construct(string $type)
	{
		$this->type = $type;
	}

	public function getType(): string
	{
		return $this->type;
	}

}
