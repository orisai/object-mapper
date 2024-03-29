<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Rules;

use Orisai\Exceptions\Logic\InvalidArgument;
use Orisai\ObjectMapper\Context\FieldContext;
use Orisai\ObjectMapper\Context\RuleArgsContext;
use Orisai\ObjectMapper\Context\TypeContext;
use Orisai\ObjectMapper\Exception\ValueDoesNotMatch;
use Orisai\ObjectMapper\Meta\Args;
use Orisai\ObjectMapper\Meta\ArgsChecker;
use Orisai\ObjectMapper\Types\SimpleValueType;
use function is_float;
use function is_int;
use function is_string;
use function preg_match;
use function sprintf;
use function str_replace;

/**
 * @phpstan-implements Rule<FloatArgs>
 */
final class FloatRule implements Rule
{

	public const
		MIN = 'min',
		MAX = 'max',
		UNSIGNED = 'unsigned',
		CAST_NUMERIC_STRING = 'castNumericString';

	/**
	 * @param array<mixed> $args
	 * @return array<mixed>
	 */
	public function resolveArgs(array $args, RuleArgsContext $context): array
	{
		$checker = new ArgsChecker($args, self::class);
		$checker->checkAllowedArgs([self::MIN, self::MAX, self::UNSIGNED, self::CAST_NUMERIC_STRING]);

		$min = null;
		if ($checker->hasArg(self::MIN)) {
			$min = $checker->checkNullableFloat(self::MIN);
		}

		$max = null;
		if ($checker->hasArg(self::MAX)) {
			$max = $checker->checkNullableFloat(self::MAX);
		}

		$unsigned = null;
		if ($checker->hasArg(self::UNSIGNED)) {
			$unsigned = $checker->checkBool(self::UNSIGNED);
		}

		if ($checker->hasArg(self::CAST_NUMERIC_STRING)) {
			$checker->checkBool(self::CAST_NUMERIC_STRING);
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

		return $args;
	}

	public function getArgsType(): string
	{
		return FloatArgs::class;
	}

	/**
	 * @param mixed $value
	 * @param FloatArgs $args
	 * @throws ValueDoesNotMatch
	 */
	public function processValue($value, Args $args, FieldContext $context): float
	{
		$initValue = $value;

		if (is_int($value)) {
			$value = (float) $value;
		}

		if (!is_float($value)) {
			$value = $this->tryConvert($value, $args);
		}

		if (!is_float($value)) {
			throw ValueDoesNotMatch::create($this->createType($args, $context), $value);
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

			throw ValueDoesNotMatch::create($type, $initValue);
		}

		return $value;
	}

	/**
	 * @param FloatArgs $args
	 */
	public function createType(Args $args, TypeContext $context): SimpleValueType
	{
		$type = new SimpleValueType('float');

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
	 * @param mixed $value
	 * @return mixed
	 */
	private function tryConvert($value, FloatArgs $args)
	{
		if ($args->castNumericString && is_string($value)) {
			// 1. Normalize commas to dots (decimals separator)
			// 2. Remove regular spaces
			$value = str_replace([',', ' '], ['.', ''], $value);

			if (preg_match('#^[+-]?[0-9]*[.]?[0-9]+\z#', $value) === 1) {
				return (float) $value;
			}
		}

		return $value;
	}

}
