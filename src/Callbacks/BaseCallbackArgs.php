<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Callbacks;

use Orisai\ObjectMapper\Meta\Args;
use function array_key_exists;

final class BaseCallbackArgs implements Args
{

	public bool $isStatic;
	public bool $returnsValue;
	public string $method;
	public string $runtime = CallbackRuntime::ALWAYS;

	private function __construct()
	{
	}

	/**
	 * @param array<mixed> $args
	 */
	public static function fromArray(array $args): self
	{
		$self = new self();

		$self->method = $args[BaseCallback::METHOD];
		$self->isStatic = $args[BaseCallback::METHOD_IS_STATIC];
		$self->returnsValue = $args[BaseCallback::METHOD_RETURNS_VALUE];

		if (array_key_exists(BaseCallback::RUNTIME, $args)) {
			$self->runtime = $args[BaseCallback::RUNTIME];
		}

		return $self;
	}

}
