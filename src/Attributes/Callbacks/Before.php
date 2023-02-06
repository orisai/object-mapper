<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Attributes\Callbacks;

use Attribute;
use Doctrine\Common\Annotations\Annotation\NamedArgumentConstructor;
use Doctrine\Common\Annotations\Annotation\Target;
use Orisai\ObjectMapper\Callbacks\BeforeCallback;
use Orisai\ObjectMapper\Callbacks\CallbackRuntime;

/**
 * @Annotation
 * @NamedArgumentConstructor()
 * @Target({"CLASS", "PROPERTY"})
 */
#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_PROPERTY)]
final class Before implements CallableAttribute
{

	private string $method;

	/** @phpstan-var CallbackRuntime::Process|CallbackRuntime::ProcessWithoutMapping|CallbackRuntime::Always */
	private string $runtime;

	/**
	 * @phpstan-param CallbackRuntime::Process|CallbackRuntime::ProcessWithoutMapping|CallbackRuntime::Always $runtime
	 */
	public function __construct(string $method, string $runtime = CallbackRuntime::Process)
	{
		$this->method = $method;
		$this->runtime = $runtime;
	}

	public function getType(): string
	{
		return BeforeCallback::class;
	}

	public function getArgs(): array
	{
		return [
			'method' => $this->method,
			'runtime' => $this->runtime,
		];
	}

}
