<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Rules;

use Orisai\ObjectMapper\Args\Args;
use Orisai\ObjectMapper\Args\ArgsChecker;
use Orisai\ObjectMapper\Exception\ValueDoesNotMatch;
use Orisai\ObjectMapper\Meta\Context\MetaFieldContext;
use Orisai\ObjectMapper\Processing\Context\DynamicContext;
use Orisai\ObjectMapper\Processing\Context\PropertyContext;
use Orisai\ObjectMapper\Processing\Context\ServicesContext;
use Orisai\ObjectMapper\Processing\Value;
use Orisai\ObjectMapper\Types\SimpleValueType;
use function is_bool;
use function is_int;
use function is_string;
use function strtolower;

/**
 * @implements Rule<BoolArgs>
 */
final class BoolRule implements Rule
{

	public const CastBoolLike = 'castBoolLike';

	private const CastMap = [
		'true' => true,
		'false' => false,
		1 => true,
		0 => false,
	];

	public function resolveArgs(array $args, MetaFieldContext $context): BoolArgs
	{
		$checker = new ArgsChecker($args, self::class);
		$checker->checkAllowedArgs([self::CastBoolLike]);

		$castBoolLike = false;
		if ($checker->hasArg(self::CastBoolLike)) {
			$castBoolLike = $checker->checkBool(self::CastBoolLike);
		}

		return new BoolArgs($castBoolLike);
	}

	public function getArgsType(): string
	{
		return BoolArgs::class;
	}

	/**
	 * @param mixed    $value
	 * @param BoolArgs $args
	 * @throws ValueDoesNotMatch
	 */
	public function processValue(
		$value,
		Args $args,
		ServicesContext $services,
		PropertyContext $property,
		DynamicContext $dynamic
	): bool
	{
		$initValue = $value;

		if (!is_bool($value)) {
			$value = $this->tryConvert($value, $args);
		}

		if (is_bool($value)) {
			return $value;
		}

		throw ValueDoesNotMatch::create($this->createType($args, $services, $dynamic), Value::of($initValue));
	}

	public function createType(
		Args $args,
		ServicesContext $services,
		DynamicContext $dynamic
	): SimpleValueType
	{
		$type = new SimpleValueType('bool');

		if ($args->castBoolLike) {
			$type->addKeyParameter('acceptsBoolLike');
		}

		return $type;
	}

	/**
	 * @param mixed $value
	 * @return mixed
	 */
	private function tryConvert($value, BoolArgs $args)
	{
		if ($args->castBoolLike) {
			if (is_string($value)) {
				$value = strtolower($value);
			}

			if (
				(is_string($value) || is_int($value))
				&& isset(self::CastMap[$value])
			) {
				return self::CastMap[$value];
			}
		}

		return $value;
	}

}
