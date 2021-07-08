<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Annotation\Docs;

use Doctrine\Common\Annotations\Annotation\NamedArgumentConstructor;
use Doctrine\Common\Annotations\Annotation\Target;
use Orisai\ObjectMapper\Annotation\AnnotationFilter;
use Orisai\ObjectMapper\Docs\DescriptionDoc;

/**
 * @Annotation
 * @NamedArgumentConstructor()
 * @Target({"CLASS", "PROPERTY"})
 */
final class Description implements DocumentationAnnotation
{

	private string $message;

	public function __construct(string $message)
	{
		$this->message = AnnotationFilter::filterMultilineDocblock($message);
	}

	public function getType(): string
	{
		return DescriptionDoc::class;
	}

	/**
	 * @return array<mixed>
	 */
	public function getArgs(): array
	{
		return [
			'message' => $this->message,
		];
	}

}
