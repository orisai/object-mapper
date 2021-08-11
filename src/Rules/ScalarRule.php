<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Rules;

use Orisai\ObjectMapper\Context\FieldContext;
use Orisai\ObjectMapper\Context\TypeContext;
use Orisai\ObjectMapper\Exception\ValueDoesNotMatch;
use Orisai\ObjectMapper\Meta\Args;
use Orisai\ObjectMapper\NoValue;
use Orisai\ObjectMapper\Types\CompoundType;
use Orisai\ObjectMapper\Types\SimpleValueType;
use function is_scalar;

/**
 * @phpstan-implements Rule<EmptyArgs>
 */
final class ScalarRule implements Rule
{

	use NoArgsRule;

	/**
	 * @param mixed $value
	 * @param EmptyArgs $args
	 * @return int|float|string|bool
	 * @throws ValueDoesNotMatch
	 */
	public function processValue($value, Args $args, FieldContext $context)
	{
		if (is_scalar($value)) {
			return $value;
		}

		$type = $this->createType($args, $context);
		foreach ($this->getSubtypes() as $key => $subtype) {
			$type->overwriteInvalidSubtype(
				$key,
				ValueDoesNotMatch::create($subtype, NoValue::create()),
			);
		}

		throw ValueDoesNotMatch::create($type, $value);
	}

	/**
	 * @param EmptyArgs $args
	 */
	public function createType(Args $args, TypeContext $context): CompoundType
	{
		$type = new CompoundType(CompoundType::OPERATOR_OR);

		foreach ($this->getSubtypes() as $key => $subtype) {
			$type->addSubtype($key, $subtype);
		}

		return $type;
	}

	/**
	 * @return array<SimpleValueType>
	 */
	private function getSubtypes(): array
	{
		return [
			new SimpleValueType('int'),
			new SimpleValueType('float'),
			new SimpleValueType('string'),
			new SimpleValueType('bool'),
		];
	}

}
