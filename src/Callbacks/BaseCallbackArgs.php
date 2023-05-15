<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Callbacks;

use Orisai\ObjectMapper\Args\Args;

final class BaseCallbackArgs implements Args
{

	public string $method;

	public bool $isStatic;

	public bool $returnsValue;

	public CallbackRuntime $runtime;

	public function __construct(
		string $method,
		bool $isStatic,
		bool $returnsValue,
		CallbackRuntime $runtime
	)
	{
		$this->method = $method;
		$this->isStatic = $isStatic;
		$this->returnsValue = $returnsValue;
		$this->runtime = $runtime;
	}

}
