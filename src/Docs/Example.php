<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Docs;

use Attribute;
use Doctrine\Common\Annotations\Annotation\NamedArgumentConstructor;
use Doctrine\Common\Annotations\Annotation\Target;
use Orisai\ReflectionMeta\Filter\AnnotationFilter;

/**
 * @Annotation
 * @NamedArgumentConstructor()
 * @Target({"CLASS", "PROPERTY"})
 */
#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_PROPERTY)]
final class Example extends DocDefinition
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

	public function getScope(): string
	{
		return $this->getHandler();
	}

	public function getHandler(): string
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
