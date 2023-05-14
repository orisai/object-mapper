<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Callbacks;

use Attribute;
use Doctrine\Common\Annotations\Annotation\NamedArgumentConstructor;
use Doctrine\Common\Annotations\Annotation\Target;

/**
 * @implements CallbackDefinition<AfterCallback>
 *
 * @Annotation
 * @NamedArgumentConstructor()
 * @Target({"CLASS", "PROPERTY"})
 */
#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_PROPERTY)]
final class After implements CallbackDefinition
{

	private string $method;

	/** @var CallbackRuntime::Process|CallbackRuntime::ProcessWithoutMapping|CallbackRuntime::Always */
	private string $runtime;

	/**
	 * @param CallbackRuntime::Process|CallbackRuntime::ProcessWithoutMapping|CallbackRuntime::Always $runtime
	 */
	public function __construct(string $method, string $runtime = CallbackRuntime::Process)
	{
		$this->method = $method;
		$this->runtime = $runtime;
	}

	public function getType(): string
	{
		return AfterCallback::class;
	}

	public function getArgs(): array
	{
		return [
			'method' => $this->method,
			'runtime' => $this->runtime,
		];
	}

}
