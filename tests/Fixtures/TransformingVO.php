<?php declare(strict_types = 1);

namespace Tests\Orisai\ObjectMapper\Fixtures;

use Orisai\ObjectMapper\Annotation\Expect;
use Orisai\ObjectMapper\ValueObject;
use stdClass;

final class TransformingVO extends ValueObject
{

	/** @Expect\BoolValue(castBoolLike=true) */
	public bool $bool;

	/** @Expect\IntValue(castIntLike=true) */
	public int $int;

	/** @Expect\FloatValue(castFloatLike=true) */
	public float $float;

	/**
	 * @Expect\AnyOf(
	 *     @Expect\InstanceValue(stdClass::class),
	 *     @Expect\NullValue(castEmptyString=true)
	 * )
	 */
	public ?stdClass $stdClassOrNull;

}
