<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Rules;

use Nette\Utils\Strings;
use Orisai\Exceptions\Logic\InvalidArgument;
use Orisai\ObjectMapper\Args\Args;
use Orisai\ObjectMapper\Args\ArgsChecker;
use Orisai\ObjectMapper\Exception\ValueDoesNotMatch;
use Orisai\ObjectMapper\Meta\Context\MetaFieldContext;
use Orisai\ObjectMapper\Processing\Context\DynamicContext;
use Orisai\ObjectMapper\Processing\Context\PropertyContext;
use Orisai\ObjectMapper\Processing\Context\ServicesContext;
use Orisai\ObjectMapper\Processing\Value;
use Orisai\ObjectMapper\Types\SimpleValueType;
use function is_int;
use function is_string;
use function preg_match;
use function sprintf;

/**
 * @implements Rule<IntArgs>
 */
final class IntRule implements Rule
{

	public const
		Min = 'min',
		Max = 'max',
		Unsigned = 'unsigned',
		CastNumericString = 'castNumericString';

	public function resolveArgs(array $args, MetaFieldContext $context): IntArgs
	{
		$checker = new ArgsChecker($args, self::class);
		$checker->checkAllowedArgs([self::Min, self::Max, self::Unsigned, self::CastNumericString]);

		$min = null;
		if ($checker->hasArg(self::Min)) {
			$min = $checker->checkNullableInt(self::Min);
		}

		$max = null;
		if ($checker->hasArg(self::Max)) {
			$max = $checker->checkNullableInt(self::Max);
		}

		$unsigned = false;
		if ($checker->hasArg(self::Unsigned)) {
			$unsigned = $checker->checkBool(self::Unsigned);
		}

		$castNumericString = false;
		if ($checker->hasArg(self::CastNumericString)) {
			$castNumericString = $checker->checkBool(self::CastNumericString);
		}

		if ($min !== null && $max !== null && $max < $min) {
			throw InvalidArgument::create()
				->withMessage(sprintf(
					'Argument %s (%s) given to rule %s cannot be lower than %s (%s).',
					self::Max,
					$max,
					self::class,
					self::Min,
					$min,
				));
		}

		if ($unsigned === true) {
			if ($min !== null && $min < 0) {
				throw InvalidArgument::create()
					->withMessage(sprintf(
						'Argument %s (%s) given to rule %s cannot be lower than 0 without %s=false',
						self::Min,
						$min,
						self::class,
						self::Unsigned,
					));
			}

			if ($max !== null && $max < 0) {
				throw InvalidArgument::create()
					->withMessage(sprintf(
						'Argument %s (%s) given to rule %s cannot be lower than 0 without %s=false',
						self::Max,
						$max,
						self::class,
						self::Unsigned,
					));
			}
		}

		return new IntArgs($min, $max, $unsigned, $castNumericString);
	}

	public function getArgsType(): string
	{
		return IntArgs::class;
	}

	/**
	 * @param mixed   $value
	 * @param IntArgs $args
	 * @throws ValueDoesNotMatch
	 */
	public function processValue(
		$value,
		Args $args,
		ServicesContext $services,
		PropertyContext $property,
		DynamicContext $dynamic
	): int
	{
		$initValue = $value;

		if ($args->castNumericString && is_string($value)) {
			$value = $this->tryConvert($value);
		}

		if (!is_int($value)) {
			throw ValueDoesNotMatch::create($this->createType($args, $services, $dynamic), Value::of($initValue));
		}

		$invalidParameters = [];

		if ($args->unsigned && $value < 0) {
			$invalidParameters[] = self::Unsigned;
		}

		if ($args->min !== null && $args->min > $value) {
			$invalidParameters[] = self::Min;
		}

		if ($args->max !== null && $args->max < $value) {
			$invalidParameters[] = self::Max;
		}

		if ($invalidParameters !== []) {
			$type = $this->createType($args, $services, $dynamic);
			$type->markParametersInvalid($invalidParameters);

			throw ValueDoesNotMatch::create($type, Value::of($initValue));
		}

		return $value;
	}

	public function createType(
		Args $args,
		ServicesContext $services,
		DynamicContext $dynamic
	): SimpleValueType
	{
		$type = new SimpleValueType('int');

		if ($args->unsigned) {
			$type->addKeyParameter('unsigned');
		}

		if ($args->min !== null) {
			$type->addKeyValueParameter('min', $args->min);
		}

		if ($args->max !== null) {
			$type->addKeyValueParameter('max', $args->max);
		}

		if ($args->castNumericString) {
			$type->addKeyParameter('acceptsNumericString');
		}

		return $type;
	}

	/**
	 * @return int|string
	 */
	private function tryConvert(string $value)
	{
		$originalValue = $value;

		$value = Strings::trim($value);
		if (preg_match('#^[+-]?[0-9]+\z#', $value) === 1) {
			return (int) $value;
		}

		return $originalValue;
	}

}
