<?php declare(strict_types = 1);

namespace Tests\Orisai\ObjectMapper\Doubles\PhpVersionSpecific;

use Orisai\ObjectMapper\Attributes\Expect\MappedObjectValue;
use Orisai\ObjectMapper\Attributes\Expect\NullValue;
use Orisai\ObjectMapper\Attributes\Expect\StringValue;
use Orisai\ObjectMapper\Attributes\Modifiers\CreateWithoutConstructor;
use Orisai\ObjectMapper\MappedObject;
use Tests\Orisai\ObjectMapper\Doubles\DefaultsVO;

#[CreateWithoutConstructor]
final class ConstructorPromotedVO implements MappedObject
{

	/**
	 * @param null $requiredUntyped
	 * @param null $optionalUntyped
	 */
	public function __construct(
		#[StringValue]
		public string $requiredString,
		#[MappedObjectValue(DefaultsVO::class)]
		public DefaultsVO $requiredObject,
		#[NullValue]
		public $requiredUntyped,
		#[StringValue]
		public string $optionalString = 'default',
		#[MappedObjectValue(DefaultsVO::class)]
		public DefaultsVO $optionalObject = new DefaultsVO(),
		#[NullValue]
		public $optionalUntyped = null,
	)
	{
	}

}
