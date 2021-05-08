<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Meta;

use Orisai\ObjectMapper\Callbacks\Callback;

final class CallbackMeta
{

	/** @var class-string<Callback> */
	private string $type;

	/** @var array<mixed> */
	private array $args;

	private function __construct()
	{
		// Static constructor is required
	}

	/**
	 * @param array<mixed> $callbackMeta
	 */
	public static function fromArray(array $callbackMeta): self
	{
		$self = new self();
		$self->type = $callbackMeta[MetaSource::OPTION_TYPE];
		$self->args = $callbackMeta[MetaSource::OPTION_ARGS] ?? [];

		return $self;
	}

	/**
	 * @return class-string<Callback>
	 */
	public function getType(): string
	{
		return $this->type;
	}

	/**
	 * @return array<mixed>
	 */
	public function getArgs(): array
	{
		return $this->args;
	}

}
