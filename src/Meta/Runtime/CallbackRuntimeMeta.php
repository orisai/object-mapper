<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Meta\Runtime;

use Orisai\ObjectMapper\Args\Args;
use Orisai\ObjectMapper\Callbacks\Callback;
use Orisai\ObjectMapper\MappedObject;
use ReflectionClass;

/**
 * @template-covariant T of Args
 */
final class CallbackRuntimeMeta
{

	/** @var class-string<Callback<T>> */
	private string $type;

	/** @var T */
	private Args $args;

	/** @var ReflectionClass<MappedObject> */
	private ReflectionClass $declaringClass;

	/**
	 * @param class-string<Callback<T>> $type
	 * @param T $args
	 * @param ReflectionClass<MappedObject> $declaringClass
	 */
	public function __construct(string $type, Args $args, ReflectionClass $declaringClass)
	{
		$this->type = $type;
		$this->args = $args;
		$this->declaringClass = $declaringClass;
	}

	/**
	 * @return class-string<Callback<T>>
	 */
	public function getType(): string
	{
		return $this->type;
	}

	/**
	 * @return T
	 */
	public function getArgs(): Args
	{
		return $this->args;
	}

	/**
	 * @return ReflectionClass<MappedObject>
	 */
	public function getDeclaringClass(): ReflectionClass
	{
		return $this->declaringClass;
	}

	/**
	 * @return array<mixed>
	 */
	public function __serialize(): array
	{
		return [
			'type' => $this->type,
			'args' => $this->args,
			'declaringClass' => $this->declaringClass->getName(),
		];
	}

	/**
	 * @param array<mixed> $data
	 */
	public function __unserialize(array $data): void
	{
		$this->type = $data['type'];
		$this->args = $data['args'];
		$this->declaringClass = new ReflectionClass($data['declaringClass']);
	}

}
