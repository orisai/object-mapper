<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Attributes\Docs;

use Doctrine\Common\Annotations\Annotation\NamedArgumentConstructor;
use Doctrine\Common\Annotations\Annotation\Target;
use Orisai\ObjectMapper\Docs\LinkDoc;

/**
 * @Annotation
 * @NamedArgumentConstructor()
 * @Target({"ANNOTATION"})
 */
final class Link implements DocumentationAttribute
{

	private string $url;

	private ?string $description;

	public function __construct(string $url, ?string $description = null)
	{
		$this->url = $url;
		$this->description = $description;
	}

	public function getType(): string
	{
		return LinkDoc::class;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getArgs(): array
	{
		return [
			'url' => $this->url,
			'description' => $this->description,
		];
	}

}
