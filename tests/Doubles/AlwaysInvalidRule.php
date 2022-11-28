<?php declare(strict_types = 1);

namespace Tests\Orisai\ObjectMapper\Doubles;

use Orisai\ObjectMapper\Args\Args;
use Orisai\ObjectMapper\Args\EmptyArgs;
use Orisai\ObjectMapper\Context\FieldContext;
use Orisai\ObjectMapper\Context\TypeContext;
use Orisai\ObjectMapper\Exception\ValueDoesNotMatch;
use Orisai\ObjectMapper\PhpTypes\Node;
use Orisai\ObjectMapper\PhpTypes\SimpleNode;
use Orisai\ObjectMapper\Rules\NoArgsRule;
use Orisai\ObjectMapper\Rules\Rule;
use Orisai\ObjectMapper\Types\MessageType;
use Orisai\ObjectMapper\Types\Value;

/**
 * @phpstan-implements Rule<EmptyArgs>
 */
final class AlwaysInvalidRule implements Rule
{

	use NoArgsRule;

	/**
	 * @param mixed     $value
	 * @param EmptyArgs $args
	 * @return mixed
	 * @throws ValueDoesNotMatch
	 */
	public function processValue($value, Args $args, FieldContext $context)
	{
		throw ValueDoesNotMatch::create($this->createType($args, $context), Value::of($value));
	}

	/**
	 * @param EmptyArgs $args
	 */
	public function createType(Args $args, TypeContext $context): MessageType
	{
		return new MessageType('Always invalid');
	}

	/**
	 * @param EmptyArgs $args
	 */
	public function getExpectedInputType(Args $args, TypeContext $context): Node
	{
		return new SimpleNode('TODO');
	}

	/**
	 * @param EmptyArgs $args
	 */
	public function getReturnType(Args $args, TypeContext $context): Node
	{
		return new SimpleNode('TODO');
	}

}
