<?php declare(strict_types = 1);

namespace Tests\Orisai\ObjectMapper\Doubles;

use Orisai\ObjectMapper\MappedObject;
use Orisai\ObjectMapper\Rules\AnyOf;
use Orisai\ObjectMapper\Rules\MappedObjectValue;
use Orisai\ObjectMapper\Rules\NullValue;
use Orisai\ObjectMapper\Rules\StringValue;

final class PropertiesInitVO implements MappedObject
{

	/** @MappedObjectValue(EmptyVO::class) */
	public EmptyVO $structure;

	/**
	 * @AnyOf({
	 *     @StringValue(),
	 *     @NullValue(),
	 * })
	 */
	public ?string $required;

	/**
	 * @AnyOf({
	 *     @StringValue(),
	 *     @NullValue(),
	 * })
	 */
	public ?string $optional = null;

	public function __construct(EmptyVO $structure, ?string $required, ?string $optional = null)
	{
		$this->structure = $structure;
		$this->required = $required;
		$this->optional = $optional;
	}

}
