<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Callbacks;

use Attribute;
use Doctrine\Common\Annotations\Annotation\NamedArgumentConstructor;
use Doctrine\Common\Annotations\Annotation\Target;

/**
 * @Annotation
 * @NamedArgumentConstructor()
 * @Target({"CLASS"})
 */
#[Attribute(Attribute::TARGET_CLASS)]
final class AfterMapping extends CallbackDefinition
{

	private string $method;

	public function __construct(string $method)
	{
		$this->method = $method;
	}

	public function getScope(): string
	{
		return $this->getHandler();
	}

	public function getHandler(): string
	{
		return AfterMappingCallback::class;
	}

	public function getArgs(): array
	{
		return [
			AfterMappingCallback::Method => $this->method,
		];
	}

}
