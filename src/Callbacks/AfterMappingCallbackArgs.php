<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Callbacks;

use Orisai\ObjectMapper\Args\Args;

final class AfterMappingCallbackArgs implements Args
{

	public string $method;

	public function __construct(string $method)
	{
		$this->method = $method;
	}

}
