<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Types;

final class SimpleValueType extends ParametrizedType
{

	private string $name;

	public function __construct(string $name)
	{
		$this->name = $name;
	}

	public function getName(): string
	{
		return $this->name;
	}

}
