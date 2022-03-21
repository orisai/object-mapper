<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Attributes\Callbacks;

use Doctrine\Common\Annotations\Annotation\NamedArgumentConstructor;
use Doctrine\Common\Annotations\Annotation\Target;
use Orisai\ObjectMapper\Callbacks\AfterCallback;
use Orisai\ObjectMapper\Callbacks\CallbackRuntime;

/**
 * @Annotation
 * @NamedArgumentConstructor()
 * @Target({"CLASS", "PROPERTY"})
 */
final class After implements CallableAttribute
{

	private string $method;

	/** @phpstan-var CallbackRuntime::* */
	private string $runtime;

	/**
	 * @phpstan-param CallbackRuntime::* $runtime
	 */
	public function __construct(string $method, string $runtime = CallbackRuntime::ALWAYS)
	{
		$this->method = $method;
		$this->runtime = $runtime;
	}

	public function getType(): string
	{
		return AfterCallback::class;
	}

	/**
	 * @return array<mixed>
	 */
	public function getArgs(): array
	{
		return [
			'method' => $this->method,
			'runtime' => $this->runtime,
		];
	}

}
