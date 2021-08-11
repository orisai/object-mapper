<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Rules;

use Orisai\Exceptions\Logic\InvalidArgument;
use Orisai\ObjectMapper\Context\FieldContext;
use Orisai\ObjectMapper\Context\RuleArgsContext;
use Orisai\ObjectMapper\Context\TypeContext;
use Orisai\ObjectMapper\Exception\ValueDoesNotMatch;
use Orisai\ObjectMapper\Meta\Args;
use Orisai\ObjectMapper\Meta\ArgsChecker;
use Orisai\ObjectMapper\Types\EnumType;
use function array_keys;
use function array_values;
use function gettype;
use function in_array;
use function is_scalar;
use function sprintf;

/**
 * @phpstan-implements Rule<ValueEnumArgs>
 */
final class ValueEnumRule implements Rule
{

	public const
		VALUES = 'values',
		USE_KEYS = 'use_keys';

	/**
	 * @param array<mixed> $args
	 * @return array<mixed>
	 */
	public function resolveArgs(array $args, RuleArgsContext $context): array
	{
		$checker = new ArgsChecker($args, self::class);
		$checker->checkAllowedArgs([self::VALUES, self::USE_KEYS]);

		$checker->checkRequiredArg(self::VALUES);
		$values = $checker->checkArray(self::VALUES);

		foreach ($values as $value) {
			if (!is_scalar($value) && $value !== null) {
				throw InvalidArgument::create()
					->withMessage(sprintf(
						'Argument "%s" given to "%s" expected to be array of "%s", one of values was "%s".',
						self::VALUES,
						self::class,
						'string|int|float|bool|null',
						gettype($value),
					));
			}
		}

		if ($checker->hasArg(self::USE_KEYS)) {
			$checker->checkBool(self::USE_KEYS);
		}

		return $args;
	}

	public function getArgsType(): string
	{
		return ValueEnumArgs::class;
	}

	/**
	 * @param mixed $value
	 * @param ValueEnumArgs $args
	 * @return mixed
	 * @throws ValueDoesNotMatch
	 */
	public function processValue($value, Args $args, FieldContext $context)
	{
		if (in_array($value, $this->getEnumValues($args), true)) {
			return $value;
		}

		throw ValueDoesNotMatch::create($this->createType($args, $context), $value);
	}

	/**
	 * @param ValueEnumArgs $args
	 */
	public function createType(Args $args, TypeContext $context): EnumType
	{
		return new EnumType($this->getEnumValues($args));
	}

	/**
	 * @return array<mixed>
	 */
	private function getEnumValues(ValueEnumArgs $args): array
	{
		return $args->useKeys
			? array_keys($args->values)
			: array_values($args->values);
	}

}
