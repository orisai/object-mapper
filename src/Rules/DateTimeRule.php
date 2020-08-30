<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Rules;

use DateTimeImmutable;
use DateTimeInterface;
use Nette\Utils\Validators;
use Orisai\Exceptions\Logic\InvalidArgument;
use Orisai\ObjectMapper\Context\FieldContext;
use Orisai\ObjectMapper\Context\RuleArgsContext;
use Orisai\ObjectMapper\Context\TypeContext;
use Orisai\ObjectMapper\Exception\ValueDoesNotMatch;
use Orisai\ObjectMapper\Meta\Args;
use Orisai\ObjectMapper\Meta\ArgsChecker;
use Orisai\ObjectMapper\Types\SimpleValueType;
use Throwable;
use function implode;
use function in_array;
use function is_int;
use function is_string;
use function sprintf;

/**
 * @implements Rule<DateTimeArgs>
 */
final class DateTimeRule implements Rule
{

	public const FORMAT = 'format';
	public const FORMAT_TIMESTAMP = 'timestamp';
	public const FORMATS_DATETIME = [
		DateTimeInterface::ATOM,
		DateTimeInterface::COOKIE,
		DateTimeInterface::RFC822,
		DateTimeInterface::RFC850,
		DateTimeInterface::RFC1036,
		DateTimeInterface::RFC1123,
		DateTimeInterface::RFC2822,
		DateTimeInterface::RFC3339,
		DateTimeInterface::RFC3339_EXTENDED,
		DateTimeInterface::RFC7231,
		DateTimeInterface::RSS,
		DateTimeInterface::W3C,
		self::FORMAT_TIMESTAMP,
	];

	/**
	 * @param array<mixed> $args
	 * @return array<mixed>
	 */
	public function resolveArgs(array $args, RuleArgsContext $context): array
	{
		$checker = new ArgsChecker($args, self::class);
		$checker->checkAllowedArgs([self::FORMAT]);

		if ($checker->hasArg(self::FORMAT)) {
			$format = $args[self::FORMAT];

			if ($format !== null && !in_array($format, self::FORMATS_DATETIME, true)) {
				throw InvalidArgument::create()
					->withMessage(sprintf(
						'Argument %s given to rule %s expected to be %s or one of formats defined in %s constants (%s)',
						self::FORMAT,
						self::class,
						self::FORMAT_TIMESTAMP,
						DateTimeInterface::class,
						implode(', ', self::FORMATS_DATETIME),
					));
			}
		}

		return $args;
	}

	public function getArgsType(): string
	{
		return DateTimeArgs::class;
	}

	/**
	 * @param mixed $value
	 * @param DateTimeArgs $args
	 * @return DateTimeImmutable|string|int
	 * @throws ValueDoesNotMatch
	 */
	public function processValue($value, Args $args, FieldContext $context)
	{
		if (!is_string($value) && !is_int($value)) {
			throw ValueDoesNotMatch::create($this->createType($args, $context));
		}

		$format = $args->format;
		$isTimestamp = false;

		if ($format === self::FORMAT_TIMESTAMP || ($format === null && Validators::isNumericInt($value))) {
			$isTimestamp = true;
		}

		$stringValue = is_int($value) ? (string) $value : $value;

		if ($isTimestamp) {
			$datetime = DateTimeImmutable::createFromFormat('U', $stringValue);
		} elseif ($format === null) {
			try {
				$datetime = new DateTimeImmutable($stringValue);
			} catch (Throwable $exception) {
				throw ValueDoesNotMatch::create($this->createType($args, $context));
			}
		} else {
			$datetime = DateTimeImmutable::createFromFormat($format, $stringValue);
		}

		if ($datetime !== false) {
			if ($context->isInitializeObjects()) {
				return $datetime;
			}

			return $value;
		}

		throw ValueDoesNotMatch::create($this->createType($args, $context));
	}

	/**
	 * @param DateTimeArgs $args
	 */
	public function createType(Args $args, TypeContext $context): SimpleValueType
	{
		if ($args->format === self::FORMAT_TIMESTAMP) {
			return new SimpleValueType('timestamp');
		}

		$type = new SimpleValueType('datetime');

		if ($args->format !== null) {
			$type->addKeyValueParameter('format', $args->format);
		}

		return $type;
	}

}
