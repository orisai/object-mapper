<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Types;

final class SimpleValueType extends ParametrizedType
{

	private string $name;

	public function __construct(string $type)
	{
		$this->name = $type;
	}

	public function getName(): string
	{
		return $this->name;
	}

}
