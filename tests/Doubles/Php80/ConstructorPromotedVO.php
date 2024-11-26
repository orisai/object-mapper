<?php declare(strict_types = 1);

namespace Tests\Orisai\ObjectMapper\Doubles\Php80;

use Orisai\ObjectMapper\MappedObject;
use Orisai\ObjectMapper\Rules\MappedObjectValue;
use Orisai\ObjectMapper\Rules\NullValue;
use Orisai\ObjectMapper\Rules\StringValue;
use Tests\Orisai\ObjectMapper\Doubles\DefaultsVO;

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
		#[NullValue]
		public $optionalUntyped = null,
	)
	{
	}

}
