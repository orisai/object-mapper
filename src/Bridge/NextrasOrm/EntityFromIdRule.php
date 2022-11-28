<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Bridge\NextrasOrm;

use Nextras\Orm\Entity\IEntity;
use Nextras\Orm\Model\IModel;
use Orisai\Exceptions\Logic\InvalidArgument;
use Orisai\ObjectMapper\Args\Args;
use Orisai\ObjectMapper\Args\ArgsChecker;
use Orisai\ObjectMapper\Context\FieldContext;
use Orisai\ObjectMapper\Context\RuleArgsContext;
use Orisai\ObjectMapper\Context\TypeContext;
use Orisai\ObjectMapper\Exception\InvalidData;
use Orisai\ObjectMapper\Exception\ValueDoesNotMatch;
use Orisai\ObjectMapper\Meta\Compile\RuleCompileMeta;
use Orisai\ObjectMapper\PhpTypes\Node;
use Orisai\ObjectMapper\PhpTypes\SimpleNode;
use Orisai\ObjectMapper\Rules\Rule;
use Orisai\ObjectMapper\Types\SimpleValueType;
use Orisai\ObjectMapper\Types\Value;
use ReflectionClass;
use Throwable;
use function is_string;
use function is_subclass_of;

/**
 * @implements Rule<EntityFromIdArgs>
 */
final class EntityFromIdRule implements Rule
{

	private const Name = 'name',
		Entity = 'entity',
		IdRule = 'idRule';

	private IModel $model;

	public function __construct(IModel $model)
	{
		$this->model = $model;
	}

	public function resolveArgs(array $args, RuleArgsContext $context): Args
	{
		$checker = new ArgsChecker($args, self::class);
		$checker->checkAllowedArgs([self::Name, self::Entity, self::IdRule]);

		$checker->checkRequiredArg(self::Name);
		$name = $checker->checkString(self::Name);

		$checker->checkRequiredArg(self::Entity);
		$entity = $args[self::Entity];

		$entityInterface = IEntity::class;
		if (
			!is_string($entity)
			|| !is_subclass_of($entity, $entityInterface)
			|| (new ReflectionClass($entity))->isAbstract()
		) {
			throw InvalidArgument::create()
				->withMessage($checker->formatMessage(
					"non-abstract class-string<$entityInterface>",
					self::Entity,
					$entity,
				));
		}

		try {
			$this->model->getRepositoryForEntity($entity);
		} catch (Throwable $exception) {
			throw InvalidArgument::create()
				->withMessage("Cannot find repository for entity $entity")
				->withSuppressed([$exception]);
		}

		$resolver = $context->getMetaResolver();

		$checker->checkRequiredArg(self::IdRule);
		$idRule = $checker->checkInstanceOf(self::IdRule, RuleCompileMeta::class);
		$idRuleMeta = $resolver->resolveRuleMeta($idRule, $context);

		return new EntityFromIdArgs($name, $entity, $idRuleMeta);
	}

	public function getArgsType(): string
	{
		return EntityFromIdArgs::class;
	}

	/**
	 * @param mixed            $value
	 * @param EntityFromIdArgs $args
	 * @return mixed
	 */
	public function processValue($value, Args $args, FieldContext $context)
	{
		$id = $this->getId($value, $args, $context);

		$repository = $this->model->getRepositoryForEntity($args->entity);
		$entity = $repository->getById($id);

		if ($entity === null) {
			throw ValueDoesNotMatch::create($this->createType($args, $context), Value::of($value));
		}

		return $context->shouldMapDataToObjects()
			? $entity
			: $value;
	}

	/**
	 * @param mixed $value
	 * @return mixed
	 * @throws ValueDoesNotMatch
	 * @throws InvalidData
	 */
	private function getId($value, EntityFromIdArgs $args, FieldContext $context)
	{
		$itemMeta = $args->idRule;
		$itemRule = $context->getRule($itemMeta->getType());
		$itemArgs = $itemMeta->getArgs();

		return $itemRule->processValue($value, $itemArgs, $context);
	}

	/**
	 * @param EntityFromIdArgs $args
	 */
	public function createType(Args $args, TypeContext $context): SimpleValueType
	{
		return new SimpleValueType($args->name);
	}

	/**
	 * @param EntityFromIdArgs $args
	 */
	public function getExpectedInputType(Args $args, TypeContext $context): Node
	{
		$ruleMeta = $args->idRule;
		$rule = $context->getRule($ruleMeta->getType());
		$ruleArgs = $ruleMeta->getArgs();

		return $rule->getExpectedInputType($ruleArgs, $context);
	}

	/**
	 * @param EntityFromIdArgs $args
	 */
	public function getReturnType(Args $args, TypeContext $context): Node
	{
		return new SimpleNode($args->entity);
	}

}
