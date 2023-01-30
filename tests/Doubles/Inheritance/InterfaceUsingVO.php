<?php declare(strict_types = 1);

namespace Tests\Orisai\ObjectMapper\Doubles\Inheritance;

use Orisai\ObjectMapper\Attributes\Expect\StringValue;

final class InterfaceUsingVO implements InterfaceForVO
{

	/** @StringValue() */
	public string $a;

	/** @StringValue() */
	public string $b;

	public function after(array $data): array
	{
		$data['b'] = 'callback';

		return $data;
	}

}
