<?php declare(strict_types = 1);

namespace Tests\Orisai\ObjectMapper\Doubles;

use Orisai\ObjectMapper\Annotation\Expect\AnyOf;
use Orisai\ObjectMapper\Annotation\Expect\BoolValue;
use Orisai\ObjectMapper\Annotation\Expect\FloatValue;
use Orisai\ObjectMapper\Annotation\Expect\InstanceValue;
use Orisai\ObjectMapper\Annotation\Expect\IntValue;
use Orisai\ObjectMapper\Annotation\Expect\NullValue;
use Orisai\ObjectMapper\ValueObject;
use stdClass;

final class TransformingVO extends ValueObject
{

	/** @BoolValue(castBoolLike=true) */
	public bool $bool;

	/** @IntValue(castNumericString=true) */
	public int $int;

	/** @FloatValue(castNumericString=true) */
	public float $float;

	/**
	 * @AnyOf(
	 *     @InstanceValue(stdClass::class),
	 *     @NullValue(castEmptyString=true)
	 * )
	 */
	public ?stdClass $stdClassOrNull;

}
