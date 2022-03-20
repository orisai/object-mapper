<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Rules;

use Orisai\ObjectMapper\Args\Args;
use Orisai\ObjectMapper\Args\ArgsChecker;
use Orisai\ObjectMapper\Context\FieldContext;
use Orisai\ObjectMapper\Context\RuleArgsContext;
use Orisai\ObjectMapper\Context\TypeContext;
use Orisai\ObjectMapper\Exception\ValueDoesNotMatch;
use Orisai\ObjectMapper\Types\SimpleValueType;
use function is_string;
use function preg_match;

/**
 * @phpstan-implements Rule<NullArgs>
 */
final class NullRule implements Rule
{

	public const CAST_EMPTY_STRING = 'castEmptyString';

	/**
	 * {@inheritDoc}
	 */
	public function resolveArgs(array $args, RuleArgsContext $context): NullArgs
	{
		$checker = new ArgsChecker($args, self::class);
		$checker->checkAllowedArgs([self::CAST_EMPTY_STRING]);

		if ($checker->hasArg(self::CAST_EMPTY_STRING)) {
			$checker->checkBool(self::CAST_EMPTY_STRING);
		}

		return NullArgs::fromArray($args);
	}

	public function getArgsType(): string
	{
		return NullArgs::class;
	}

	/**
	 * @param mixed $value
	 * @param NullArgs $args
	 * @return null
	 * @throws ValueDoesNotMatch
	 */
	public function processValue($value, Args $args, FieldContext $context)
	{
		if ($value !== null) {
			$value = $this->tryConvert($value, $args);
		}

		if ($value !== null) {
			throw ValueDoesNotMatch::create($this->createType($args, $context), $value);
		}

		return $value;
	}

	/**
	 * @param NullArgs $args
	 */
	public function createType(Args $args, TypeContext $context): SimpleValueType
	{
		$type = new SimpleValueType('null');

		if ($args->castEmptyString) {
			$type->addKeyParameter('acceptsEmptyString');
		}

		return $type;
	}

	/**
	 * @param mixed $value
	 * @return mixed
	 */
	private function tryConvert($value, NullArgs $args)
	{
		if ($args->castEmptyString && is_string($value) && preg_match('/\S/', $value) !== 1) {
			return null;
		}

		return $value;
	}

}
