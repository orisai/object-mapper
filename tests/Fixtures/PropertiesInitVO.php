<?php declare(strict_types = 1);

namespace Tests\Orisai\ObjectMapper\Fixtures;

use Orisai\ObjectMapper\Annotation\Expect\AnyOf;
use Orisai\ObjectMapper\Annotation\Expect\NullValue;
use Orisai\ObjectMapper\Annotation\Expect\StringValue;
use Orisai\ObjectMapper\Annotation\Expect\Structure;
use Orisai\ObjectMapper\ValueObject;

final class PropertiesInitVO extends ValueObject
{

	/**
	 * @AnyOf(
	 *     @StringValue(),
	 *     @NullValue(),
	 * )
	 */
	public ?string $required;

	/**
	 * @AnyOf(
	 *     @StringValue(),
	 *     @NullValue(),
	 * )
	 */
	public ?string $optional = null;

	/** @Structure(EmptyVO::class) */
	public EmptyVO $structure;

}
