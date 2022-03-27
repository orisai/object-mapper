<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Rules;

use Orisai\ObjectMapper\Args\Args;
use Orisai\ObjectMapper\Args\ArgsChecker;
use Orisai\ObjectMapper\Context\FieldContext;
use Orisai\ObjectMapper\Context\RuleArgsContext;
use Orisai\ObjectMapper\Context\TypeContext;
use Orisai\ObjectMapper\Exception\ValueDoesNotMatch;
use Orisai\ObjectMapper\Types\SimpleValueType;
use Orisai\ObjectMapper\Types\Value;
use function is_string;
use function mb_strlen;
use function preg_match;

/**
 * @phpstan-implements Rule<StringArgs>
 */
final class StringRule implements Rule
{

	public const
		PATTERN = 'pattern',
		MIN_LENGTH = 'minLength',
		MAX_LENGTH = 'maxLength',
		NOT_EMPTY = 'notEmpty';

	/**
	 * {@inheritDoc}
	 */
	public function resolveArgs(array $args, RuleArgsContext $context): StringArgs
	{
		$checker = new ArgsChecker($args, self::class);

		$checker->checkAllowedArgs([self::PATTERN, self::NOT_EMPTY, self::MIN_LENGTH, self::MAX_LENGTH]);

		$pattern = null;
		if ($checker->hasArg(self::PATTERN)) {
			$pattern = $checker->checkNullableString(self::PATTERN);
		}

		$notEmpty = false;
		if ($checker->hasArg(self::NOT_EMPTY)) {
			$notEmpty = $checker->checkBool(self::NOT_EMPTY);
		}

		$minLength = null;
		if ($checker->hasArg(self::MIN_LENGTH)) {
			$minLength = $checker->checkNullableInt(self::MIN_LENGTH);
		}

		$maxLength = null;
		if ($checker->hasArg(self::MAX_LENGTH)) {
			$maxLength = $checker->checkNullableInt(self::MAX_LENGTH);
		}

		return new StringArgs($pattern, $notEmpty, $minLength, $maxLength);
	}

	public function getArgsType(): string
	{
		return StringArgs::class;
	}

	/**
	 * @param mixed $value
	 * @param StringArgs $args
	 * @throws ValueDoesNotMatch
	 */
	public function processValue($value, Args $args, FieldContext $context): string
	{
		if (!is_string($value)) {
			throw ValueDoesNotMatch::create($this->createType($args, $context), Value::of($value));
		}

		$invalidParameters = [];

		if ($args->notEmpty && preg_match('/\S/', $value) !== 1) {
			$invalidParameters[] = self::NOT_EMPTY;
		}

		if ($args->minLength !== null && $args->minLength > mb_strlen($value)) {
			$invalidParameters[] = self::MIN_LENGTH;
		}

		if ($args->maxLength !== null && $args->maxLength < mb_strlen($value)) {
			$invalidParameters[] = self::MAX_LENGTH;
		}

		if ($args->pattern !== null && preg_match($args->pattern, $value) !== 1) {
			$invalidParameters[] = self::PATTERN;
		}

		if ($invalidParameters !== []) {
			$type = $this->createType($args, $context);
			$type->markParametersInvalid($invalidParameters);

			throw ValueDoesNotMatch::create($type, Value::of($value));
		}

		return $value;
	}

	/**
	 * @param StringArgs $args
	 */
	public function createType(Args $args, TypeContext $context): SimpleValueType
	{
		$type = new SimpleValueType('string');

		if ($args->notEmpty) {
			$type->addKeyParameter('notEmpty');
		}

		if ($args->minLength !== null) {
			$type->addKeyValueParameter('minLength', $args->minLength);
		}

		if ($args->maxLength !== null) {
			$type->addKeyValueParameter('maxLength', $args->maxLength);
		}

		if ($args->pattern !== null) {
			$type->addKeyValueParameter('pattern', $args->pattern);
		}

		return $type;
	}

}
