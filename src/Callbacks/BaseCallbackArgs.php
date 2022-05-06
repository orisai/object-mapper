<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Callbacks;

use Orisai\ObjectMapper\Args\Args;

final class BaseCallbackArgs implements Args
{

	public string $method;

	public bool $isStatic;

	public bool $returnsValue;

	public string $runtime;

	public function __construct(
		string $method,
		bool $isStatic,
		bool $returnsValue,
		string $runtime = CallbackRuntime::Always
	)
	{
		$this->method = $method;
		$this->isStatic = $isStatic;
		$this->returnsValue = $returnsValue;
		$this->runtime = $runtime;
	}

}
