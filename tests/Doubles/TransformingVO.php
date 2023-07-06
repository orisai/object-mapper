<?php declare(strict_types = 1);

namespace Tests\Orisai\ObjectMapper\Doubles;

use Orisai\ObjectMapper\MappedObject;
use Orisai\ObjectMapper\Rules\AnyOf;
use Orisai\ObjectMapper\Rules\BoolValue;
use Orisai\ObjectMapper\Rules\FloatValue;
use Orisai\ObjectMapper\Rules\InstanceOfValue;
use Orisai\ObjectMapper\Rules\IntValue;
use Orisai\ObjectMapper\Rules\NullValue;
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

	public function __construct(bool $bool, int $int, float $float, ?stdClass $stdClassOrNull)
	{
		$this->bool = $bool;
		$this->int = $int;
		$this->float = $float;
		$this->stdClassOrNull = $stdClassOrNull;
	}

}
