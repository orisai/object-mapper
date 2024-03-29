<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Rules;

use Orisai\Exceptions\Logic\InvalidArgument;
use Orisai\ObjectMapper\Args\Args;
use Orisai\ObjectMapper\Args\ArgsChecker;
use Orisai\ObjectMapper\Context\ArgsContext;
use Orisai\ObjectMapper\Context\FieldContext;
use Orisai\ObjectMapper\Context\TypeContext;
use Orisai\ObjectMapper\Exception\ValueDoesNotMatch;
use Orisai\ObjectMapper\Processing\Value;
use Orisai\ObjectMapper\Types\EnumType;
use function array_keys;
use function array_values;
use function gettype;
use function in_array;
use function is_scalar;
use function sprintf;

/**
 * @implements Rule<ArrayEnumArgs>
 */
final class ArrayEnumRule implements Rule
{

	private const
		Cases = 'cases',
		UseKeys = 'useKeys',
		AllowUnknown = 'allowUnknown';

	public function resolveArgs(array $args, ArgsContext $context): ArrayEnumArgs
	{
		$checker = new ArgsChecker($args, self::class);
		$checker->checkAllowedArgs([self::Cases, self::UseKeys, self::AllowUnknown]);

		$checker->checkRequiredArg(self::Cases);
		$cases = $checker->checkArray(self::Cases);

		foreach ($cases as $case) {
			if (!is_scalar($case) && $case !== null) {
				throw InvalidArgument::create()
					->withMessage(sprintf(
						'Argument "%s" given to "%s" expected to be array of "%s", one of values was "%s".',
						self::Cases,
						self::class,
						'string|int|float|bool|null',
						gettype($case),
					));
			}
		}

		$useKeys = false;
		if ($checker->hasArg(self::UseKeys)) {
			$useKeys = $checker->checkBool(self::UseKeys);
		}

		$allowUnknown = false;
		if ($checker->hasArg(self::AllowUnknown)) {
			$allowUnknown = $checker->checkBool(self::AllowUnknown);
		}

		return new ArrayEnumArgs($cases, $useKeys, $allowUnknown);
	}

	public function getArgsType(): string
	{
		return ArrayEnumArgs::class;
	}

	/**
	 * @param mixed         $value
	 * @param ArrayEnumArgs $args
	 * @return mixed
	 * @throws ValueDoesNotMatch
	 */
	public function processValue($value, Args $args, FieldContext $context)
	{
		if (in_array($value, $this->getEnumCases($args), true)) {
			return $value;
		}

		if ($args->allowUnknown) {
			return null;
		}

		throw ValueDoesNotMatch::create($this->createType($args, $context), Value::of($value));
	}

	/**
	 * @param ArrayEnumArgs $args
	 */
	public function createType(Args $args, TypeContext $context): EnumType
	{
		return new EnumType($this->getEnumCases($args));
	}

	/**
	 * @return array<mixed>
	 */
	private function getEnumCases(ArrayEnumArgs $args): array
	{
		return $args->useKeys
			? array_keys($args->cases)
			: array_values($args->cases);
	}

}
