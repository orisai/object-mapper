<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Rules;

use Orisai\ObjectMapper\Args\Args;
use Orisai\ObjectMapper\Args\ArgsChecker;
use Orisai\ObjectMapper\Context\FieldContext;
use Orisai\ObjectMapper\Context\RuleArgsContext;
use Orisai\ObjectMapper\Context\TypeContext;
use Orisai\ObjectMapper\Exception\InvalidData;
use Orisai\ObjectMapper\MappedObject;
use Orisai\ObjectMapper\Modifiers\FieldNameArgs;
use Orisai\ObjectMapper\Modifiers\FieldNameModifier;
use Orisai\ObjectMapper\Types\MappedObjectType;
use function array_keys;
use function assert;
use function is_a;

/**
 * @phpstan-implements Rule<MappedObjectArgs>
 */
final class MappedObjectRule implements Rule
{

	public const TYPE = 'type';

	public function resolveArgs(array $args, RuleArgsContext $context): MappedObjectArgs
	{
		$checker = new ArgsChecker($args, self::class);

		$checker->checkAllowedArgs([self::TYPE]);

		$checker->checkRequiredArg(self::TYPE);
		$type = $checker->checkString(self::TYPE);

		// Load object to ensure whole hierarchy is valid even if not used
		// Note: Loading as class should be always array cached and in runtime should be metadata resolved only once so it has no performance impact
		$context->getMetaLoader()->load($args[self::TYPE]);
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
			$propertyRuleMeta = $propertyMeta->getRule();
			$propertyRule = $context->getRule($propertyRuleMeta->getType());
			$propertyArgs = $propertyRuleMeta->getArgs();

			$fieldNameMeta = $propertyMeta->getModifier(FieldNameModifier::class);
			if ($fieldNameMeta !== null) {
				$args = $fieldNameMeta->getArgs();
				assert($args instanceof FieldNameArgs);
				$fieldName = $args->name;
			} else {
				$fieldName = $propertyName;
			}

			$type->addField(
				$fieldName,
				$propertyRule->createType($propertyArgs, $context),
			);
		}

		return $type;
	}

}
