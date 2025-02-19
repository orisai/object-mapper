<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Rules;

use Nette\Utils\Strings;
use Orisai\ObjectMapper\Args\Args;
use Orisai\ObjectMapper\Args\ArgsChecker;
use Orisai\ObjectMapper\Exception\ValueDoesNotMatch;
use Orisai\ObjectMapper\Meta\Context\MetaFieldContext;
use Orisai\ObjectMapper\Processing\Context\DynamicContext;
use Orisai\ObjectMapper\Processing\Context\PropertyContext;
use Orisai\ObjectMapper\Processing\Context\ServicesContext;
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

	public const
		Pattern = 'pattern',
		MinLength = 'minLength',
		MaxLength = 'maxLength',
		NotEmpty = 'notEmpty',
		Trim = 'trim';

	public function resolveArgs(array $args, MetaFieldContext $context): StringArgs
	{
		$checker = new ArgsChecker($args, self::class);

		$checker->checkAllowedArgs([self::Pattern, self::NotEmpty, self::MinLength, self::MaxLength, self::Trim]);

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

		$trim = false;
		if ($checker->hasArg(self::Trim)) {
			$trim = $checker->checkBool(self::Trim);
		}

		return new StringArgs($pattern, $notEmpty, $minLength, $maxLength, $trim);
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
	public function processValue(
		$value,
		Args $args,
		ServicesContext $services,
		PropertyContext $property,
		DynamicContext $dynamic
	): string
	{
		if (!is_string($value)) {
			throw ValueDoesNotMatch::create($this->createType($args, $services, $dynamic), Value::of($value));
		}

		$trimmedValue = null;
		if ($args->trim) {
			$trimmedValue = $value = Strings::trim($value);
		}

		$invalidParameters = [];

		if ($args->notEmpty && ($trimmedValue ?? Strings::trim($value)) === '') {
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
			$type = $this->createType($args, $services, $dynamic);
			$type->markParametersInvalid($invalidParameters);

			throw ValueDoesNotMatch::create($type, Value::of($value));
		}

		return $value;
	}

	public function createType(
		Args $args,
		ServicesContext $services,
		DynamicContext $dynamic
	): SimpleValueType
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
