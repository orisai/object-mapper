<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Bridge\NextrasOrm;

use Nextras\Orm\Entity\IEntity;
use Nextras\Orm\Model\IModel;
use Nextras\Orm\Repository\IRepository;
use Orisai\Exceptions\Logic\InvalidArgument;
use Orisai\ObjectMapper\Args\Args;
use Orisai\ObjectMapper\Args\ArgsChecker;
use Orisai\ObjectMapper\Context\FieldContext;
use Orisai\ObjectMapper\Context\RuleArgsContext;
use Orisai\ObjectMapper\Context\TypeContext;
use Orisai\ObjectMapper\Exception\ValueDoesNotMatch;
use Orisai\ObjectMapper\Meta\Compile\RuleCompileMeta;
use Orisai\ObjectMapper\Rules\MultiValueEfficientRule;
use Orisai\ObjectMapper\Types\SimpleValueType;
use Orisai\ObjectMapper\Types\Value;
use ReflectionClass;
use Throwable;
use function is_string;
use function is_subclass_of;

/**
 * @implements MultiValueEfficientRule<EntityFromIdArgs>
 */
final class EntityFromIdRule implements MultiValueEfficientRule
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
		$id = $this->processValuePhase1($value, $args, $context);

		return $this->processValuePhase3($id, $args, $context);
	}

	/**
	 * @param EntityFromIdArgs $args
	 */
	public function processValuePhase1($value, Args $args, FieldContext $context)
	{
		$itemMeta = $args->idRule;
		$itemRule = $context->getRule($itemMeta->getType());
		$itemArgs = $itemMeta->getArgs();

		return $itemRule->processValue($value, $itemArgs, $context);
	}

	/**
	 * @param EntityFromIdArgs $args
	 */
	public function processValuePhase2(array $values, Args $args, FieldContext $context): void
	{
		$repository = $this->getRepository($args);
		$repository->findByIds([$values]);
	}

	/**
	 * @param EntityFromIdArgs $args
	 */
	public function processValuePhase3($value, Args $args, FieldContext $context)
	{
		$repository = $this->getRepository($args);
		$entity = $repository->getById($value);

		if ($entity === null) {
			throw ValueDoesNotMatch::create($this->createType($args, $context), Value::of($value));
		}

		return $context->shouldMapDataToObjects()
			? $entity
			: $value;
	}

	/**
	 * @param EntityFromIdArgs $args
	 */
	public function createType(Args $args, TypeContext $context): SimpleValueType
	{
		return new SimpleValueType($args->name);
	}

	private function getRepository(EntityFromIdArgs $args): IRepository
	{
		return $this->model->getRepositoryForEntity($args->entity);
	}

}
