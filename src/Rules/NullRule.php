<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Rules;

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
use function preg_match;

/**
 * @implements Rule<NullArgs>
 */
final class NullRule implements Rule
{

	public const CastEmptyString = 'castEmptyString';

	public function resolveArgs(array $args, MetaFieldContext $context): NullArgs
	{
		$checker = new ArgsChecker($args, self::class);
		$checker->checkAllowedArgs([self::CastEmptyString]);

		$castEmptyString = false;
		if ($checker->hasArg(self::CastEmptyString)) {
			$castEmptyString = $checker->checkBool(self::CastEmptyString);
		}

		return new NullArgs($castEmptyString);
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
	public function processValue(
		$value,
		Args $args,
		ServicesContext $services,
		PropertyContext $property,
		DynamicContext $dynamic
	)
	{
		if ($value !== null) {
			$value = $this->tryConvert($value, $args);
		}

		if ($value !== null) {
			throw ValueDoesNotMatch::create($this->createType($args, $services, $dynamic), Value::of($value));
		}

		return $value;
	}

	public function createType(
		Args $args,
		ServicesContext $services,
		DynamicContext $dynamic
	): SimpleValueType
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
