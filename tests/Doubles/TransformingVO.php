<?php declare(strict_types = 1);

namespace Tests\Orisai\ObjectMapper\Doubles;

use Orisai\ObjectMapper\Attributes\Expect\AnyOf;
use Orisai\ObjectMapper\Attributes\Expect\BoolValue;
use Orisai\ObjectMapper\Attributes\Expect\FloatValue;
use Orisai\ObjectMapper\Attributes\Expect\InstanceOfValue;
use Orisai\ObjectMapper\Attributes\Expect\IntValue;
use Orisai\ObjectMapper\Attributes\Expect\NullValue;
use Orisai\ObjectMapper\MappedObject;
use stdClass;

final class TransformingVO implements MappedObject
{

	/** @BoolValue(castBoolLike=true) */
	public bool $bool;

	/** @IntValue(castNumericString=true) */
	public int $int;

	/** @FloatValue(castNumericString=true) */
	public float $float;

	/**
	 * @AnyOf({
	 *     @InstanceOfValue(stdClass::class),
	 *     @NullValue(castEmptyString=true)
	 * })
	 */
	public ?stdClass $stdClassOrNull;

}
