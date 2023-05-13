<?php declare(strict_types = 1);

namespace Tests\Orisai\ObjectMapper\Unit\Processing;

use Orisai\Exceptions\Logic\InvalidState;
use Orisai\ObjectMapper\Processing\RawValuesMap;
use PHPUnit\Framework\TestCase;
use Tests\Orisai\ObjectMapper\Doubles\DefaultsVO;
use function serialize;
use function unserialize;
use const PHP_VERSION_ID;

final class RawValuesMapTest extends TestCase
{

	public function testSet(): void
	{
		$map = new RawValuesMap();

		$object = new DefaultsVO();
		$values = 'foo';
		$map->setRawValues($object, $values);
		self::assertSame($values, $map->getRawValues($object));
	}

	public function testNotSet(): void
	{
		$map = new RawValuesMap();
		$object = new DefaultsVO();

		$this->expectException(InvalidState::class);
		$this->expectExceptionMessage(
			<<<'MSG'
Context: Getting raw values for object of type
         'Tests\Orisai\ObjectMapper\Doubles\DefaultsVO'.
Problem: Raw values are not set.
Solution: Ensure 'Orisai\ObjectMapper\Processing\Options::setTrackRawValues()'
          is enabled and that object was processed in current request by object
          mapper (raw values are available only as long as reference to object
          exists).
MSG,
		);

		$map->getRawValues($object);
	}

	public function testUnset(): void
	{
		if (PHP_VERSION_ID < 8_00_00) {
			self::markTestSkipped('Polyfill for PHP < 8.0 does not clear memory immediately.');
		}

		$map = new RawValuesMap();

		$object = new DefaultsVO();
		$map->setRawValues($object, 'foo');

		$data = serialize($object);
		unset($object);
		$object = unserialize($data);

		$this->expectException(InvalidState::class);
		$map->getRawValues($object);
	}

}
