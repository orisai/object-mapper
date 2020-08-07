<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Rules;

use Orisai\ObjectMapper\Context\FieldContext;
use Orisai\ObjectMapper\Context\RuleArgsContext;
use Orisai\ObjectMapper\Context\TypeContext;
use Orisai\ObjectMapper\Exception\ValueDoesNotMatch;
use Orisai\ObjectMapper\Meta\Args;
use Orisai\ObjectMapper\Meta\ArgsChecker;
use Orisai\ObjectMapper\Types\SimpleValueType;
use function is_bool;
use function is_int;
use function is_string;
use function strtolower;

/**
 * @implements Rule<BoolArgs>
 */
final class BoolRule implements Rule
{

	public const CAST_BOOL_LIKE = 'castBoolLike';

	private const CASTABLE_MAP = [
		'true' => true,
		'false' => false,
		1 => true,
		0 => false,
	];

	/**
	 * @param array<mixed> $args
	 * @return array<mixed>
	 */
	public function resolveArgs(array $args, RuleArgsContext $context): array
	{
		$checker = new ArgsChecker($args, self::class);
		$checker->checkAllowedArgs([self::CAST_BOOL_LIKE]);

		if ($checker->hasArg(self::CAST_BOOL_LIKE)) {
			$checker->checkBool(self::CAST_BOOL_LIKE);
		}

		return $args;
	}

	public function getArgsType(): string
	{
		return BoolArgs::class;
	}

	/**
	 * @param mixed $value
	 * @param BoolArgs $args
	 * @throws ValueDoesNotMatch
	 */
	public function processValue($value, Args $args, FieldContext $context): bool
	{
		if (!is_bool($value)) {
			$value = $this->tryConvert($value, $args);
		}

		if (is_bool($value)) {
			return $value;
		}

		throw ValueDoesNotMatch::create($this->createType($args, $context));
	}

	/**
	 * @param BoolArgs $args
	 */
	public function createType(Args $args, TypeContext $context): SimpleValueType
	{
		$parameters = [
			'acceptsBoolLike' => $args->castBoolLike,
		];

		return new SimpleValueType('bool', $parameters);
	}

	/**
	 * @param mixed $value
	 * @return mixed
	 */
	private function tryConvert($value, BoolArgs $args)
	{
		if ($args->castBoolLike) {
			if (is_string($value)) {
				$value = strtolower($value);
			}

			if (
				(is_string($value) || is_int($value))
				&& isset(self::CASTABLE_MAP[$value])
			) {
				return self::CASTABLE_MAP[$value];
			}
		}

		return $value;
	}

}
