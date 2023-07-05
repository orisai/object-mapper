<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Rules;

use Orisai\ObjectMapper\Args\Args;
use Orisai\ObjectMapper\Args\ArgsChecker;
use Orisai\ObjectMapper\Context\ArgsContext;
use Orisai\ObjectMapper\Context\FieldContext;
use Orisai\ObjectMapper\Context\TypeContext;
use Orisai\ObjectMapper\Exception\ValueDoesNotMatch;
use Orisai\ObjectMapper\Processing\Value;
use Orisai\ObjectMapper\Types\SimpleValueType;
use function is_string;
use function mb_strlen;
use function preg_match;

/**
 * @implements Rule<StringArgs>
 */
final class StringRule implements Rule
{

	/** @internal */
	public const
		Pattern = 'pattern',
		MinLength = 'minLength',
		MaxLength = 'maxLength',
		NotEmpty = 'notEmpty';

	public function resolveArgs(array $args, ArgsContext $context): StringArgs
	{
		$checker = new ArgsChecker($args, self::class);

		$checker->checkAllowedArgs([self::Pattern, self::NotEmpty, self::MinLength, self::MaxLength]);

		$pattern = null;
		if ($checker->hasArg(self::Pattern)) {
			$pattern = $checker->checkNullableString(self::Pattern);
		}

		$notEmpty = false;
		if ($checker->hasArg(self::NotEmpty)) {
			$notEmpty = $checker->checkBool(self::NotEmpty);
		}

		$minLength = null;
		if ($checker->hasArg(self::MinLength)) {
			$minLength = $checker->checkNullableInt(self::MinLength);
		}

		$maxLength = null;
		if ($checker->hasArg(self::MaxLength)) {
			$maxLength = $checker->checkNullableInt(self::MaxLength);
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
			$invalidParameters[] = self::NotEmpty;
		}

		if ($args->minLength !== null && $args->minLength > mb_strlen($value)) {
			$invalidParameters[] = self::MinLength;
		}

		if ($args->maxLength !== null && $args->maxLength < mb_strlen($value)) {
			$invalidParameters[] = self::MaxLength;
		}

		if ($args->pattern !== null && preg_match($args->pattern, $value) !== 1) {
			$invalidParameters[] = self::Pattern;
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
