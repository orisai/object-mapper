<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Rules;

use Orisai\Exceptions\Logic\InvalidArgument;
use Orisai\ObjectMapper\Args\Args;
use Orisai\ObjectMapper\Args\ArgsChecker;
use Orisai\ObjectMapper\Context\FieldContext;
use Orisai\ObjectMapper\Context\RuleArgsContext;
use Orisai\ObjectMapper\Context\TypeContext;
use Orisai\ObjectMapper\Exceptions\ValueDoesNotMatch;
use Orisai\ObjectMapper\Types\SimpleValueType;
use Orisai\ObjectMapper\Types\Value;
use function is_int;
use function is_string;
use function preg_match;
use function sprintf;
use function str_replace;

/**
 * @phpstan-implements Rule<IntArgs>
 */
final class IntRule implements Rule
{

	public const
		MIN = 'min',
		MAX = 'max',
		UNSIGNED = 'unsigned',
		CAST_NUMERIC_STRING = 'castNumericString';

	/**
	 * {@inheritDoc}
	 */
	public function resolveArgs(array $args, RuleArgsContext $context): IntArgs
	{
		$checker = new ArgsChecker($args, self::class);
		$checker->checkAllowedArgs([self::MIN, self::MAX, self::UNSIGNED, self::CAST_NUMERIC_STRING]);

		$min = null;
		if ($checker->hasArg(self::MIN)) {
			$min = $checker->checkNullableInt(self::MIN);
		}

		$max = null;
		if ($checker->hasArg(self::MAX)) {
			$max = $checker->checkNullableInt(self::MAX);
		}

		$unsigned = true;
		if ($checker->hasArg(self::UNSIGNED)) {
			$unsigned = $checker->checkBool(self::UNSIGNED);
		}

		$castNumericString = false;
		if ($checker->hasArg(self::CAST_NUMERIC_STRING)) {
			$castNumericString = $checker->checkBool(self::CAST_NUMERIC_STRING);
		}

		if ($min !== null && $max !== null && $max < $min) {
			throw InvalidArgument::create()
				->withMessage(sprintf(
					'Argument %s (%s) given to rule %s cannot be lower than %s (%s).',
					self::MAX,
					$max,
					self::class,
					self::MIN,
					$min,
				));
		}

		if ($unsigned === true) {
			if ($min !== null && $min < 0) {
				throw InvalidArgument::create()
					->withMessage(sprintf(
						'Argument %s (%s) given to rule %s cannot be lower than 0 without %s=false',
						self::MIN,
						$min,
						self::class,
						self::UNSIGNED,
					));
			}

			if ($max !== null && $max < 0) {
				throw InvalidArgument::create()
					->withMessage(sprintf(
						'Argument %s (%s) given to rule %s cannot be lower than 0 without %s=false',
						self::MAX,
						$max,
						self::class,
						self::UNSIGNED,
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
	public function processValue($value, Args $args, FieldContext $context): int
	{
		$initValue = $value;

		if ($args->castNumericString && is_string($value)) {
			$value = $this->tryConvert($value);
		}

		if (!is_int($value)) {
			throw ValueDoesNotMatch::create($this->createType($args, $context), Value::of($value));
		}

		$invalidParameters = [];

		if ($args->unsigned && $value < 0) {
			$invalidParameters[] = self::UNSIGNED;
		}

		if ($args->min !== null && $args->min > $value) {
			$invalidParameters[] = self::MIN;
		}

		if ($args->max !== null && $args->max < $value) {
			$invalidParameters[] = self::MAX;
		}

		if ($invalidParameters !== []) {
			$type = $this->createType($args, $context);
			$type->markParametersInvalid($invalidParameters);

			throw ValueDoesNotMatch::create($type, Value::of($initValue));
		}

		return $value;
	}

	/**
	 * @param IntArgs $args
	 */
	public function createType(Args $args, TypeContext $context): SimpleValueType
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
		// Remove regular spaces
		$value = str_replace(' ', '', $value);

		if (preg_match('#^[+-]?[0-9]+\z#', $value) === 1) {
			return (int) $value;
		}

		return $value;
	}

}
