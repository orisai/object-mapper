<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Meta\Runtime;

use Orisai\ObjectMapper\Args\Args;
use Orisai\ObjectMapper\Meta\Shared\DefaultValueMeta;

final class FieldRuntimeMeta implements NodeRuntimeMeta
{

	/** @var RuleRuntimeMeta<Args> */
	public RuleRuntimeMeta $rule;

	public DefaultValueMeta $default;

	public PhpPropertyMeta $property;

	/** @var list<CallbackRuntimeMeta<Args>> */
	private array $beforeValidationCallbacks;

	/** @var list<CallbackRuntimeMeta<Args>> */
	private array $afterValidationCallbacks;

	/**
	 * @template T_ARGS of Args
	 * @param list<CallbackRuntimeMeta<T_ARGS>> $beforeValidationCallbacks
	 * @param list<CallbackRuntimeMeta<T_ARGS>> $afterValidationCallbacks
	 * @param RuleRuntimeMeta<Args> $rule
	 */
	public function __construct(
		array $beforeValidationCallbacks,
		array $afterValidationCallbacks,
		RuleRuntimeMeta $rule,
		DefaultValueMeta $default,
		PhpPropertyMeta $property
	)
	{
		$this->rule = $rule;
		$this->default = $default;
		$this->property = $property;
		$this->beforeValidationCallbacks = $beforeValidationCallbacks;
		$this->afterValidationCallbacks = $afterValidationCallbacks;
	}

	public function getBeforeValidationCallbacks(): array
	{
		return $this->beforeValidationCallbacks;
	}

	public function getAfterValidationCallbacks(): array
	{
		return $this->afterValidationCallbacks;
	}

	/**
	 * @return array<mixed>
	 */
	public function __serialize(): array
	{
		return [
			'rule' => $this->rule,
			'default' => $this->default,
			'property' => $this->property,
			'beforeValidationCallbacks' => $this->beforeValidationCallbacks,
			'afterValidationCallbacks' => $this->afterValidationCallbacks,
		];
	}

	/**
	 * @param array<mixed> $data
	 */
	public function __unserialize(array $data): void
	{
		$this->rule = $data['rule'];
		$this->default = $data['default'];
		$this->property = $data['property'];
		$this->beforeValidationCallbacks = $data['beforeValidationCallbacks'];
		$this->afterValidationCallbacks = $data['afterValidationCallbacks'];
	}

}
