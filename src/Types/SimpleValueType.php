<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Types;

final class SimpleValueType extends ParametrizedType
{

	private string $type;

	/**
	 * @param array<mixed> $parameters
	 */
	public function __construct(string $type, array $parameters = [])
	{
		$this->type = $type;
		$this->parameters = $parameters;
	}

	public function getType(): string
	{
		return $this->type;
	}

}
