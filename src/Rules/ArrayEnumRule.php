<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Rules;

use Orisai\Exceptions\Logic\InvalidArgument;
use Orisai\ObjectMapper\Args\Args;
use Orisai\ObjectMapper\Args\ArgsChecker;
use Orisai\ObjectMapper\Context\FieldContext;
use Orisai\ObjectMapper\Context\RuleArgsContext;
use Orisai\ObjectMapper\Context\TypeContext;
use Orisai\ObjectMapper\Exception\ValueDoesNotMatch;
use Orisai\ObjectMapper\PhpTypes\CompoundNode;
use Orisai\ObjectMapper\PhpTypes\LiteralNode;
use Orisai\ObjectMapper\PhpTypes\Node;
use Orisai\ObjectMapper\Types\EnumType;
use Orisai\ObjectMapper\Types\Value;
use function array_keys;
use function array_values;
use function gettype;
use function in_array;
use function is_scalar;
use function sprintf;

/**
 * @phpstan-implements Rule<ArrayEnumArgs>
 */
final class ArrayEnumRule implements Rule
{

	private const
		Values = 'values',
		UseKeys = 'useKeys';

	public function resolveArgs(array $args, RuleArgsContext $context): ArrayEnumArgs
	{
		$checker = new ArgsChecker($args, self::class);
		$checker->checkAllowedArgs([self::Values, self::UseKeys]);

		$checker->checkRequiredArg(self::Values);
		$values = $checker->checkArray(self::Values);

		foreach ($values as $value) {
			if (!is_scalar($value) && $value !== null) {
				throw InvalidArgument::create()
					->withMessage(sprintf(
						'Argument "%s" given to "%s" expected to be array of "%s", one of values was "%s".',
						self::Values,
						self::class,
						'string|int|float|bool|null',
						gettype($value),
					));
			}
		}

		$useKeys = false;
		if ($checker->hasArg(self::UseKeys)) {
			$useKeys = $checker->checkBool(self::UseKeys);
		}

		return new ArrayEnumArgs($values, $useKeys);
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
		if (in_array($value, $this->getEnumValues($args), true)) {
			return $value;
		}

		throw ValueDoesNotMatch::create($this->createType($args, $context), Value::of($value));
	}

	/**
	 * @param ArrayEnumArgs $args
	 */
	public function createType(Args $args, TypeContext $context): EnumType
	{
		return new EnumType($this->getEnumValues($args));
	}

	/**
	 * @return array<mixed>
	 */
	private function getEnumValues(ArrayEnumArgs $args): array
	{
		return $args->useKeys
			? array_keys($args->values)
			: array_values($args->values);
	}

	/**
	 * @param ArrayEnumArgs $args
	 */
	public function getExpectedInputType(Args $args, TypeContext $context): Node
	{
		$types = [];
		foreach ($this->getEnumValues($args) as $value) {
			$types[] = new LiteralNode($value);
		}

		return CompoundNode::createOrType($types);
	}

	/**
	 * @param ArrayEnumArgs $args
	 */
	public function getReturnType(Args $args, TypeContext $context): Node
	{
		return $this->getExpectedInputType($args, $context);
	}

}
