<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Docs;

use Doctrine\Common\Annotations\Annotation\NamedArgumentConstructor;
use Doctrine\Common\Annotations\Annotation\Target;
use Orisai\ObjectMapper\Meta\Source\AnnotationFilter;

/**
 * @Annotation
 * @NamedArgumentConstructor()
 * @Target({"ANNOTATION"})
 */
final class Example implements DocumentationAttribute
{

	private string $content;

	private ?string $description;

	public function __construct(string $content, ?string $description = null)
	{
		$this->content = AnnotationFilter::filterMultilineDocblock($content);
		$this->description = $description === null
			? null
			: AnnotationFilter::filterMultilineDocblock($description);
	}

	public function getType(): string
	{
		return ExampleDoc::class;
	}

	public function getArgs(): array
	{
		return [
			'content' => $this->content,
			'description' => $this->description,
		];
	}

}
