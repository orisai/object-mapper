<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Rules;

use Closure;
use Orisai\ObjectMapper\Args\Args;
use Orisai\ObjectMapper\Args\ArgsChecker;
use Orisai\ObjectMapper\Context\ArgsContext;
use Orisai\ObjectMapper\Context\FieldContext;
use Orisai\ObjectMapper\Context\TypeContext;
use Orisai\ObjectMapper\Exception\InvalidData;
use Orisai\ObjectMapper\MappedObject;
use Orisai\ObjectMapper\Meta\Runtime\FieldRuntimeMeta;
use Orisai\ObjectMapper\Types\MappedObjectType;
use Orisai\ObjectMapper\Types\Type;
use Throwable;
use function array_key_exists;
use function assert;
use function in_array;
use function is_a;

/**
 * @implements Rule<MappedObjectArgs>
 */
final class MappedObjectRule implements Rule
{

	private const ClassName = 'class';

	/** @var array<string, null> */
	private array $alreadyResolved = [];

	public function resolveArgs(array $args, ArgsContext $context): MappedObjectArgs
	{
		$checker = new ArgsChecker($args, self::class);

		$checker->checkAllowedArgs([self::ClassName]);

		$checker->checkRequiredArg(self::ClassName);
		$type = $checker->checkString(self::ClassName);

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

		$options = $context->getOptions()->createClone();

		return $context->shouldInitializeObjects()
			? $processor->process($value, $args->class, $options)
			: $processor->processWithoutMapping($value, $args->class, $options);
	}

	/**
	 * @param MappedObjectArgs $args
	 */
	public function createType(Args $args, TypeContext $context): MappedObjectType
	{
		if (in_array($args->class, $context->getProcessedClasses(), true)) {
			return new MappedObjectType($args->class);
		}

		$type = new MappedObjectType($args->class);
		foreach ($context->getMeta($args->class)->getFields() as $fieldName => $fieldMeta) {
			$type->addField(
				$fieldName,
				$this->getTypeCreator($fieldMeta, $context, $args),
			);
		}

		return $type;
	}

	/**
	 * @return Closure(): Type
	 */
	private function getTypeCreator(
		FieldRuntimeMeta $fieldMeta,
		TypeContext $context,
		MappedObjectArgs $args
	): Closure
	{
		$fieldRuleMeta = $fieldMeta->getRule();
		$fieldRule = $context->getRule($fieldRuleMeta->getType());
		$fieldArgs = $fieldRuleMeta->getArgs();

		return static fn (): Type => $fieldRule->createType(
			$fieldArgs,
			$context->withProcessedClass($args->class),
		);
	}

}
