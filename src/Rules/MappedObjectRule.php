<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Rules;

use Orisai\ObjectMapper\Args\Args;
use Orisai\ObjectMapper\Args\ArgsChecker;
use Orisai\ObjectMapper\Context\FieldContext;
use Orisai\ObjectMapper\Context\RuleArgsContext;
use Orisai\ObjectMapper\Context\TypeContext;
use Orisai\ObjectMapper\Exception\InvalidData;
use Orisai\ObjectMapper\MappedObject;
use Orisai\ObjectMapper\Meta\Runtime\PropertyRuntimeMeta;
use Orisai\ObjectMapper\Meta\Runtime\RuleRuntimeMeta;
use Orisai\ObjectMapper\Modifiers\FieldNameModifier;
use Orisai\ObjectMapper\PhpTypes\ClassReferenceNode;
use Orisai\ObjectMapper\PhpTypes\Node;
use Orisai\ObjectMapper\PhpTypes\SimpleNode;
use Orisai\ObjectMapper\Types\MappedObjectType;
use function array_keys;
use function assert;
use function is_a;

/**
 * @phpstan-implements Rule<MappedObjectArgs>
 */
final class MappedObjectRule implements Rule
{

	private const Type = 'type';

	public function resolveArgs(array $args, RuleArgsContext $context): MappedObjectArgs
	{
		$checker = new ArgsChecker($args, self::class);

		$checker->checkAllowedArgs([self::Type]);

		$checker->checkRequiredArg(self::Type);
		$type = $checker->checkString(self::Type);

		// Load object to ensure whole hierarchy is valid even if not used
		// Note: Loading as class should be always array cached and in runtime should be metadata resolved only once so it has no performance impact
		$context->getMetaLoader()->load($args[self::Type]);
		assert(is_a($type, MappedObject::class, true));

		return new MappedObjectArgs($type);
	}

	public function getArgsType(): string
	{
		return MappedObjectArgs::class;
	}

	/**
	 * @param mixed            $value
	 * @param MappedObjectArgs $args
	 * @return MappedObject|array<mixed>
	 * @throws InvalidData
	 */
	public function processValue($value, Args $args, FieldContext $context)
	{
		$processor = $context->getProcessor();

		return $context->shouldMapDataToObjects()
			? $processor->process($value, $args->type, $context->getOptions())
			: $processor->processWithoutMapping($value, $args->type, $context->getOptions());
	}

	/**
	 * @param MappedObjectArgs $args
	 */
	public function createType(Args $args, TypeContext $context): MappedObjectType
	{
		$propertiesMeta = $context->getMeta($args->type)->getProperties();
		$propertyNames = array_keys($propertiesMeta);

		$type = new MappedObjectType($args->type);

		foreach ($propertyNames as $propertyName) {
			$propertyMeta = $propertiesMeta[$propertyName];
			[$propertyRule, $propertyArgs] = $this->getPropertyRuleArgs($propertyMeta->getRule(), $context);

			$fieldName = $this->getFieldName($propertyMeta, $propertyName);

			$type->addField(
				$fieldName,
				$propertyRule->createType($propertyArgs, $context),
			);
		}

		return $type;
	}

	/**
	 * @param MappedObjectArgs $args
	 */
	public function getExpectedInputType(Args $args, TypeContext $context): ClassReferenceNode
	{
		$propertiesMeta = $context->getMeta($args->type)->getProperties();
		$propertyNames = array_keys($propertiesMeta);

		$structure = [];
		foreach ($propertyNames as $propertyName) {
			$propertyMeta = $propertiesMeta[$propertyName];
			[$propertyRule, $propertyArgs] = $this->getPropertyRuleArgs($propertyMeta->getRule(), $context);

			$fieldName = $this->getFieldName($propertyMeta, $propertyName);

			$structure[$fieldName] = $propertyRule->getExpectedInputType($propertyArgs, $context);
		}

		return new ClassReferenceNode($args->type, $structure);
	}

	/**
	 * @param MappedObjectArgs $args
	 */
	public function getReturnType(Args $args, TypeContext $context): Node
	{
		return new SimpleNode($args->type);
	}

	/**
	 * @param RuleRuntimeMeta<Args> $meta
	 * @return array{Rule<Args>, Args}
	 */
	private function getPropertyRuleArgs(RuleRuntimeMeta $meta, TypeContext $context): array
	{
		$rule = $context->getRule($meta->getType());
		$args = $meta->getArgs();

		return [$rule, $args];
	}

	/**
	 * @return int|string
	 */
	private function getFieldName(PropertyRuntimeMeta $propertyMeta, string $propertyName)
	{
		$fieldNameMeta = $propertyMeta->getModifier(FieldNameModifier::class);

		return $fieldNameMeta !== null
			? $fieldNameMeta->getArgs()->name
			: $propertyName;
	}

}
