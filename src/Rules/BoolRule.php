<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Rules;

use Orisai\ObjectMapper\Args\Args;
use Orisai\ObjectMapper\Args\ArgsChecker;
use Orisai\ObjectMapper\Context\FieldContext;
use Orisai\ObjectMapper\Context\RuleArgsContext;
use Orisai\ObjectMapper\Context\TypeContext;
use Orisai\ObjectMapper\Exceptions\ValueDoesNotMatch;
use Orisai\ObjectMapper\Types\SimpleValueType;
use function is_bool;
use function is_int;
use function is_string;
use function strtolower;

/**
 * @phpstan-implements Rule<BoolArgs>
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
	 * {@inheritDoc}
	 */
	public function resolveArgs(array $args, RuleArgsContext $context): BoolArgs
	{
		$checker = new ArgsChecker($args, self::class);
		$checker->checkAllowedArgs([self::CAST_BOOL_LIKE]);

		$castBoolLike = false;
		if ($checker->hasArg(self::CAST_BOOL_LIKE)) {
			$castBoolLike = $checker->checkBool(self::CAST_BOOL_LIKE);
		}

		return new BoolArgs($castBoolLike);
	}

	public function getArgsType(): string
	{
		return BoolArgs::class;
	}

	/**
	 * @param mixed    $value
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

		throw ValueDoesNotMatch::create($this->createType($args, $context), $value);
	}

	/**
	 * @param BoolArgs $args
	 */
	public function createType(Args $args, TypeContext $context): SimpleValueType
	{
		$type = new SimpleValueType('bool');

		if ($args->castBoolLike) {
			$type->addKeyParameter('acceptsBoolLike');
		}

		return $type;
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
