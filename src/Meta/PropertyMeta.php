<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Meta;

use function array_key_exists;

final class PropertyMeta extends SharedMeta
{

	private DefaultValueMeta $default;

	/** @var array<mixed> */
	private array $rule;

	private ?RuleMeta $instRule = null;

	/**
	 * @param array<mixed> $propertyMeta
	 * @return static
	 */
	public static function fromArray(array $propertyMeta): self
	{
		$self = parent::fromArray($propertyMeta);
		$self->rule = $propertyMeta[MetaSource::TYPE_RULE];
		$self->default = array_key_exists(MetaSource::TYPE_DEFAULT_VALUE, $propertyMeta)
			? DefaultValueMeta::fromValueOrNothing($propertyMeta[MetaSource::TYPE_DEFAULT_VALUE])
			: DefaultValueMeta::fromValueOrNothing();

		return $self;
	}

	public function getDefault(): DefaultValueMeta
	{
		return $this->default;
	}

	public function getRule(): RuleMeta
	{
		if ($this->instRule !== null) {
			return $this->instRule;
		}

		return $this->instRule = RuleMeta::fromArray($this->rule);
	}

}
