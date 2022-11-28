<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Rules;

use Orisai\ObjectMapper\Args\Args;
use Orisai\ObjectMapper\Args\EmptyArgs;
use Orisai\ObjectMapper\Context\FieldContext;
use Orisai\ObjectMapper\Context\TypeContext;
use Orisai\ObjectMapper\Exception\ValueDoesNotMatch;
use Orisai\ObjectMapper\PhpTypes\CompoundNode;
use Orisai\ObjectMapper\PhpTypes\Node;
use Orisai\ObjectMapper\PhpTypes\SimpleNode;
use Orisai\ObjectMapper\Types\CompoundType;
use Orisai\ObjectMapper\Types\SimpleValueType;
use Orisai\ObjectMapper\Types\Value;
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
				ValueDoesNotMatch::create($subtype, Value::none()),
			);
		}

		throw ValueDoesNotMatch::create($type, Value::of($value));
	}

	/**
	 * @param EmptyArgs $args
	 */
	public function createType(Args $args, TypeContext $context): CompoundType
	{
		$type = CompoundType::createOrType();

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

	/**
	 * @param EmptyArgs $args
	 */
	public function getExpectedInputType(Args $args, TypeContext $context): Node
	{
		return CompoundNode::createOrType([
			new SimpleNode('int'),
			new SimpleNode('float'),
			new SimpleNode('string'),
			new SimpleNode('bool'),
		]);
	}

	/**
	 * @param EmptyArgs $args
	 */
	public function getReturnType(Args $args, TypeContext $context): Node
	{
		return $this->getExpectedInputType($args, $context);
	}

}
