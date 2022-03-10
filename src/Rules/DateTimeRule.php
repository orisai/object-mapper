<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Rules;

use DateTime;
use DateTimeImmutable;
use Nette\Utils\Validators;
use Orisai\Exceptions\Logic\InvalidArgument;
use Orisai\ObjectMapper\Context\FieldContext;
use Orisai\ObjectMapper\Context\RuleArgsContext;
use Orisai\ObjectMapper\Context\TypeContext;
use Orisai\ObjectMapper\Exception\ValueDoesNotMatch;
use Orisai\ObjectMapper\Meta\Args;
use Orisai\ObjectMapper\Meta\ArgsChecker;
use Orisai\ObjectMapper\Types\SimpleValueType;
use ReflectionClass;
use Throwable;
use function class_exists;
use function is_int;
use function is_string;
use function sprintf;

/**
 * @phpstan-implements Rule<DateTimeArgs>
 */
final class DateTimeRule implements Rule
{

	public const
		FORMAT = 'format',
		TYPE = 'type';

	public const FORMAT_TIMESTAMP = 'timestamp',
		FORMAT_ANY = 'any';

	/**
	 * @param array<mixed> $args
	 * @return array<mixed>
	 */
	public function resolveArgs(array $args, RuleArgsContext $context): array
	{
		$checker = new ArgsChecker($args, self::class);
		$checker->checkAllowedArgs([self::FORMAT, self::TYPE]);

		if ($checker->hasArg(self::FORMAT)) {
			$checker->checkString(self::FORMAT);
		}

		if ($checker->hasArg(self::TYPE)) {
			$type = $args[self::TYPE];

			if (!is_string($type) || !class_exists($type) || (new ReflectionClass($type))->isAbstract()) {
				throw InvalidArgument::create()
					->withMessage($checker->formatMessage(
						sprintf(
							'%s or %s or their non-abstract child class',
							DateTimeImmutable::class,
							DateTime::class,
						),
						self::TYPE,
						$type,
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
	 * @return DateTimeImmutable|DateTime|string|int
	 * @throws ValueDoesNotMatch
	 */
	public function processValue($value, Args $args, FieldContext $context)
	{
		if (!is_string($value) && !is_int($value)) {
			throw ValueDoesNotMatch::create($this->createType($args, $context), $value);
		}

		$format = $args->format;
		$isTimestamp = false;

		if ($format === self::FORMAT_TIMESTAMP || ($format === self::FORMAT_ANY && Validators::isNumericInt($value))) {
			$isTimestamp = true;
		}

		$stringValue = is_int($value) ? (string) $value : $value;
		$type = $args->type;

		if ($isTimestamp) {
			$datetime = $type::createFromFormat('U', $stringValue);
		} elseif ($format === self::FORMAT_ANY) {
			try {
				$datetime = new $type($stringValue);
			} catch (Throwable $exception) {
				throw ValueDoesNotMatch::create($this->createType($args, $context), $value);
			}
		} else {
			$datetime = $type::createFromFormat($format, $stringValue);
		}

		if ($datetime === false) {
			throw ValueDoesNotMatch::create($this->createType($args, $context), $value);
		}

		return $context->isInitializeObjects()
			? $datetime
			: $value;
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

		if ($args->format !== self::FORMAT_ANY) {
			$type->addKeyValueParameter('format', $args->format);
		}

		return $type;
	}

}
