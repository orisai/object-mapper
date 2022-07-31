<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Printers;

final class PrinterScope
{

	private bool $renderValid;

	private bool $immutable = false;

	private function __construct(bool $renderValid)
	{
		$this->renderValid = $renderValid;
	}

	public static function forInvalidScope(): self
	{
		return new self(false);
	}

	public function withValidNodes(): self
	{
		$clone = clone $this;
		if (!$this->immutable) {
			$clone->renderValid = true;
		}

		return $clone;
	}

	public function withoutValidNodes(): self
	{
		$clone = clone $this;
		if (!$this->immutable) {
			$clone->renderValid = false;
		}

		return $clone;
	}

	public function withImmutableState(): self
	{
		$clone = clone $this;
		$clone->immutable = true;

		return $clone;
	}

	public function shouldRenderValid(): bool
	{
		return $this->renderValid;
	}

}
