<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Docs;

use Doctrine\Common\Annotations\Annotation\NamedArgumentConstructor;
use Doctrine\Common\Annotations\Annotation\Target;

/**
 * @Annotation
 * @NamedArgumentConstructor()
 * @Target({"ANNOTATION"})
 */
final class Link implements DocDefinition
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

	public function getArgs(): array
	{
		return [
			'url' => $this->url,
			'description' => $this->description,
		];
	}

}
