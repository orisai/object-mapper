<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Rules;

use DateTime;
use DateTimeImmutable;
use DateTimeInterface;
use DateTimeZone;
use Nette\Utils\Validators;
use Orisai\Exceptions\Logic\InvalidArgument;
use Orisai\ObjectMapper\Args\Args;
use Orisai\ObjectMapper\Args\ArgsChecker;
use Orisai\ObjectMapper\Context\FieldContext;
use Orisai\ObjectMapper\Context\RuleArgsContext;
use Orisai\ObjectMapper\Context\TypeContext;
use Orisai\ObjectMapper\Exception\ValueDoesNotMatch;
use Orisai\ObjectMapper\Types\SimpleValueType;
use Orisai\ObjectMapper\Types\Value;
use ReflectionClass;
use Throwable;
use function assert;
use function is_a;
use function is_int;
use function is_string;
use function sprintf;
use function strpos;
use function substr;
use const PHP_VERSION_ID;

/**
 * @implements Rule<DateTimeArgs>
 */
final class DateTimeRule implements Rule
{

	/** @internal */
	public const
		Format = 'format',
		Type = 'type';

	public const FormatTimestamp = 'timestamp',
		FormatAny = 'any',
		FormatIsoCompat = 'iso_compat';

	private const JsIsoFormat = 'Y-m-d\TH:i:s.v\Z';

	public function resolveArgs(array $args, RuleArgsContext $context): DateTimeArgs
	{
		$checker = new ArgsChecker($args, self::class);
		$checker->checkAllowedArgs([self::Format, self::Type]);

		$format = self::FormatIsoCompat;
		if ($checker->hasArg(self::Format)) {
			$format = $checker->checkString(self::Format);
		}

		$type = DateTimeImmutable::class;
		if ($checker->hasArg(self::Type)) {
			$type = $args[self::Type];

			if (
				!is_string($type)
				|| !is_a($type, DateTimeInterface::class, true)
				|| (new ReflectionClass($type))->isAbstract()
			) {
				throw InvalidArgument::create()
					->withMessage($checker->formatMessage(
						sprintf(
							'%s or %s or their non-abstract child class',
							DateTimeImmutable::class,
							DateTime::class,
						),
						self::Type,
						$type,
					));
			}
		}

		return new DateTimeArgs($type, $format);
	}

	public function getArgsType(): string
	{
		return DateTimeArgs::class;
	}

	/**
	 * @param mixed        $value
	 * @param DateTimeArgs $args
	 * @return DateTimeImmutable|DateTime|string|int
	 * @throws ValueDoesNotMatch
	 */
	public function processValue($value, Args $args, FieldContext $context)
	{
		if (!is_string($value) && !is_int($value)) {
			throw ValueDoesNotMatch::create($this->createType($args, $context), Value::of($value));
		}

		$format = $args->format;
		$isTimestamp = false;

		if ($format === self::FormatTimestamp || ($format === self::FormatAny && Validators::isNumericInt($value))) {
			$isTimestamp = true;
		}

		$stringValue = is_int($value) ? (string) $value : $value;
		$classType = $args->type;

		if ($isTimestamp) {
			$datetime = $classType::createFromFormat('U', $stringValue);
		} elseif ($format === self::FormatAny) {
			try {
				$datetime = new $classType($stringValue);
			} catch (Throwable $exception) {
				$type = $this->createType($args, $context);
				if ($type->hasParameter('format')) {
					$type->markParameterInvalid('format');
				}

				$message = $exception->getMessage();
				if (PHP_VERSION_ID < 8_01_00) {
					// Drop 'DateTimeImmutable::__construct(): ' from message start
					$pos = strpos($message, ' ');
					assert($pos !== false);
					$message = substr($message, $pos + 1);
				}

				$type->addKeyParameter($message);
				$type->markParameterInvalid($message);

				throw ValueDoesNotMatch::create($type, Value::of($value));
			}
		} elseif ($format === self::FormatIsoCompat) {
			$datetime = $stringValue !== ''
				&& substr($stringValue, -1) === 'Z'
				? $classType::createFromFormat(self::JsIsoFormat, $stringValue, new DateTimeZone('UTC'))
				: $classType::createFromFormat(
					DateTimeInterface::ATOM,
					$stringValue,
				);
		} else {
			$datetime = $classType::createFromFormat($format, $stringValue);
		}

		if ($datetime === false) {
			$errors = $args->isImmutable()
				? DateTimeImmutable::getLastErrors()
				: DateTime::getLastErrors();
			assert($errors !== false);

			$type = $this->createType($args, $context);
			if ($type->hasParameter('format')) {
				$type->markParameterInvalid('format');
			}

			foreach ($errors['errors'] as $error) {
				$type->addKeyParameter($error);
				$type->markParameterInvalid($error);
			}

			throw ValueDoesNotMatch::create($type, Value::of($value));
		}

		return $context->shouldMapDataToObjects()
			? $datetime
			: $value;
	}

	/**
	 * @param DateTimeArgs $args
	 */
	public function createType(Args $args, TypeContext $context): SimpleValueType
	{
		if ($args->format === self::FormatTimestamp) {
			return new SimpleValueType('timestamp');
		}

		$type = new SimpleValueType('datetime');

		$format = $args->format;
		if ($format === self::FormatIsoCompat) {
			$format = DateTimeInterface::ATOM . ' | ' . self::JsIsoFormat;
		}

		if ($format !== self::FormatAny) {
			$type->addKeyValueParameter('format', $format);
		}

		return $type;
	}

}
