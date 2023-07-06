<?php declare(strict_types = 1);

namespace Tests\Orisai\ObjectMapper\Doubles;

use Orisai\ObjectMapper\MappedObject;
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

	/**
	 * @param array<string> $arrayOfMixed
	 */
	public function __construct(
		string $string = 'foo',
		?string $nullableString = null,
		array $arrayOfMixed = [
			0 => 'foo',
			'bar' => 'baz',
		]
	)
	{
		$this->string = $string;
		$this->nullableString = $nullableString;
		$this->arrayOfMixed = $arrayOfMixed;
	}

}
