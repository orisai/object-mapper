<?php declare(strict_types = 1);

namespace Tests\Orisai\ObjectMapper\Doubles;

use Orisai\ObjectMapper\MappedObject;
use Orisai\ObjectMapper\Modifiers\DefaultValue;
use Orisai\ObjectMapper\Rules\AnyOf;
use Orisai\ObjectMapper\Rules\ArrayOf;
use Orisai\ObjectMapper\Rules\MixedValue;
use Orisai\ObjectMapper\Rules\NullValue;
use Orisai\ObjectMapper\Rules\StringValue;

final class DefaultsVO implements MappedObject
{

	/** @StringValue() */
	public string $string = 'foo';

	/**
	 * @DefaultValue("attribute default")
	 * @StringValue()
	 */
	public string $defaultByAttributeString = 'property default';

	/**
	 * @AnyOf({
	 *     @StringValue(),
	 *     @NullValue(),
	 * })
	 */
	public ?string $nullableString = null;

	/**
	 * @var array<string>
	 *
	 * @ArrayOf(
	 *     @MixedValue()
	 * )
	 */
	public array $arrayOfMixed = [
		0 => 'foo',
		'bar' => 'baz',
	];

}
