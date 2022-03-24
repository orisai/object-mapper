<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Bridge\NextrasOrm;

use Nextras\Orm\Entity\IEntity;
use Orisai\ObjectMapper\Attributes\Expect\RuleAttribute;
use Orisai\ObjectMapper\Meta\Compile\RuleCompileMeta;

final class EntityFromId implements RuleAttribute
{

	private string $name;

	/** @var class-string<IEntity> */
	private string $entity;

	private RuleCompileMeta $idRule;

	/**
	 * @param class-string<IEntity> $entity
	 */
	public function __construct(string $name, string $entity, RuleAttribute $idRule)
	{
		$this->name = $name;
		$this->entity = $entity;
		$this->idRule = new RuleCompileMeta($idRule->getType(), $idRule->getArgs());
	}

	public function getType(): string
	{
		return EntityFromIdRule::class;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getArgs(): array
	{
		return [
			'name' => $this->name,
			'entity' => $this->entity,
			'idRule' => $this->idRule,
		];
	}

}
