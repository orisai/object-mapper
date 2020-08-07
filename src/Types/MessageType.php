<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Types;

final class MessageType implements Type
{

	private string $message;

	public function __construct(string $message)
	{
		$this->message = $message;
	}

	public function getMessage(): string
	{
		return $this->message;
	}

}
