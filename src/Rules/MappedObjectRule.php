<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Rules;

use Orisai\ObjectMapper\Args\Args;
use Orisai\ObjectMapper\Args\ArgsChecker;
use Orisai\ObjectMapper\Context\FieldContext;
use Orisai\ObjectMapper\Context\RuleArgsContext;
use Orisai\ObjectMapper\Context\TypeContext;
use Orisai\ObjectMapper\Exception\InvalidData;
use Orisai\ObjectMapper\MappedObject;
use Orisai\ObjectMapper\Modifiers\FieldNameModifier;
use Orisai\ObjectMapper\Types\MappedObjectType;
use Orisai\ObjectMapper\Types\MessageType;
use Throwable;
use function array_key_exists;
use function array_keys;
use function assert;
use function is_a;

/**
 * @phpstan-implements Rule<MappedObjectArgs>
 */
final class MappedObjectRule implements Rule
{

	private const Type = 'type';

	/** @var array<string, null> */
	private array $alreadyResolved = [];

	public function resolveArgs(array $args, RuleArgsContext $context): MappedObjectArgs
	{
		$checker = new ArgsChecker($args, self::class);

		$checker->checkAllowedArgs([self::Type]);

		$checker->checkRequiredArg(self::Type);
		$type = $checker->checkString(self::Type);

		// Load object to ensure whole hierarchy is valid even if not used
		if (!array_key_exists($type, $this->alreadyResolved)) {
			$this->alreadyResolved[$type] = null;
			try {
				$context->getMetaLoader()->load($type);
			} catch (Throwable $e) {
				unset($this->alreadyResolved[$type]);

				throw $e;
			}
		}

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
		static $selfs = [];

		$propertiesMeta = $context->getMeta($args->type)->getProperties();
		$propertyNames = array_keys($propertiesMeta);

		$type = new MappedObjectType($args->type);

		foreach ($propertyNames as $propertyName) {
			$propertyMeta = $propertiesMeta[$propertyName];

			if (isset($selfs[$args->type][$propertyName])) {
				$propertyType = new MessageType('circular reference');
			} else {
				$selfs[$args->type][$propertyName] = 1;

				$propertyRuleMeta = $propertyMeta->getRule();
				$propertyRule = $context->getRule($propertyRuleMeta->getType());
				$propertyArgs = $propertyRuleMeta->getArgs();

				$propertyType = $propertyRule->createType($propertyArgs, $context);
			}

			unset($selfs[$args->type][$propertyName]);

			$fieldNameMeta = $propertyMeta->getModifier(FieldNameModifier::class);
			$fieldName = $fieldNameMeta !== null ? $fieldNameMeta->getArgs()->name : $propertyName;

			$type->addField($fieldName, $propertyType);
		}

		unset($selfs[$args->type]);

		return $type;
	}

}
