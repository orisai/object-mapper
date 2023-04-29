<?php declare(strict_types = 1);

namespace Tests\Orisai\ObjectMapper\Doubles\Callbacks;

use Orisai\ObjectMapper\Attributes\Callbacks\Before;
use Orisai\ObjectMapper\Attributes\Expect\MappedObjectValue;
use Orisai\ObjectMapper\MappedObject;
use Tests\Orisai\ObjectMapper\Doubles\DefaultsVO;
use function assert;

/**
 * @Before("before")
 */
final class ObjectInitializingVO implements MappedObject
{

	/** @MappedObjectValue(DefaultsVO::class) */
	public DefaultsVO $inner;

	/**
	 * @param mixed $values
	 * @return array<mixed>
	 */
	private function before($values): array
	{
		assert($values === []);

		return [
			'inner' => [],
		];
	}

}
