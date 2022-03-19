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
