<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Printers;

use Orisai\Exceptions\Logic\InvalidState;
use function array_key_first;
use function array_key_last;

class PrinterScopes
{

	private bool $isOpen = false;

	/** @var array<int, bool> */
	public array $cartridge = [];

	/** @var array<int, int> */
	public array $immutablesCartridge = [];

	public function shouldRenderValid(): bool
	{
		if ($this->cartridge === []) {
			return false;
		}

		if ($this->immutablesCartridge !== []) {
			return !$this->cartridge[array_key_first($this->immutablesCartridge)];
		}

		return !$this->cartridge[array_key_last($this->cartridge)];
	}

	public function open(): void
	{
		if ($this->isOpen || $this->cartridge !== []) {
			throw InvalidState::create()
				->withMessage('Can\'t open scopes, previous were never closed');
		}

		$this->isOpen = true;
	}

	public function close(): void
	{
		if (!$this->isOpen) {
			throw InvalidState::create()
				->withMessage('Can\'t close scopes, they were never opened');
		}

		if ($this->cartridge !== []) {
			throw InvalidState::create()
				->withMessage('Can\'t close all scopes, some individual scopes are not closed');
		}

		$this->isOpen = false;
	}

	public function openScope(bool $filterValid, bool $isImmutable = false): void
	{
		$this->cartridge[] = $filterValid;

		if ($isImmutable) {
			$key = array_key_last($this->cartridge);
			$this->immutablesCartridge[$key] = $key;
		}
	}

	public function closeScope(): void
	{
		if ($this->cartridge === []) {
			throw InvalidState::create()
				->withMessage('Can\'t close scope which was never opened');
		}

		$key = array_key_last($this->cartridge);

		unset($this->cartridge[$key], $this->immutablesCartridge[$key]);
	}

}
