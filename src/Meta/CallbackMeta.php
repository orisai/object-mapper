<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Meta;

use Orisai\ObjectMapper\Args\Args;
use Orisai\ObjectMapper\Callbacks\Callback;

final class CallbackMeta
{

	/** @var class-string<Callback<Args>> */
	private string $type;

	/** @var array<mixed> */
	private array $args;

	/**
	 * @param class-string<Callback<Args>> $type
	 * @param array<mixed>                 $args
	 */
	public function __construct(string $type, array $args)
	{
		$this->type = $type;
		$this->args = $args;
	}

	/**
	 * @param array<mixed> $callbackMeta
	 */
	public static function fromArray(array $callbackMeta): self
	{
		return new self(
			$callbackMeta[MetaSource::OPTION_TYPE],
			$callbackMeta[MetaSource::OPTION_ARGS] ?? [],
		);
	}

	/**
	 * @return array{type: class-string<Callback<Args>>, args: array<mixed>}
	 */
	public function toArray(): array
	{
		return [
			MetaSource::OPTION_TYPE => $this->type,
			MetaSource::OPTION_ARGS => $this->args,
		];
	}

	/**
	 * @return class-string<Callback<Args>>
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
