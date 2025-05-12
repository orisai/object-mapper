<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Docs;

use Attribute;
use Doctrine\Common\Annotations\Annotation\NamedArgumentConstructor;
use Doctrine\Common\Annotations\Annotation\Target;

/**
 * @Annotation
 * @NamedArgumentConstructor()
 * @Target({"CLASS", "PROPERTY"})
 */
#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_PROPERTY)]
final class Link extends DocDefinition
{

	private string $url;

	private ?string $description;

	public function __construct(string $url, ?string $description = null)
	{
		$this->url = $url;
		$this->description = $description;
	}

	public function getScope(): string
	{
		return $this->getHandler();
	}

	public function getHandler(): string
	{
		return LinkDoc::class;
	}

	public function getArgs(): array
	{
		return [
			'url' => $this->url,
			'description' => $this->description,
		];
	}

}
