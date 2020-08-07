<?php declare(strict_types = 1);

namespace Tests\Orisai\ObjectMapper\Unit\Types;

use Orisai\ObjectMapper\Types\MessageType;
use PHPUnit\Framework\TestCase;

final class MessageTypeTest extends TestCase
{

	public function test(): void
	{
		$type = new MessageType('message');

		self::assertSame('message', $type->getMessage());
	}

}
