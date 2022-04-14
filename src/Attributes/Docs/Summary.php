<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Attributes\Docs;

use Doctrine\Common\Annotations\Annotation\NamedArgumentConstructor;
use Doctrine\Common\Annotations\Annotation\Target;
use Orisai\ObjectMapper\Attributes\AnnotationFilter;
use Orisai\ObjectMapper\Docs\SummaryDoc;

/**
 * @Annotation
 * @NamedArgumentConstructor()
 * @Target({"CLASS", "PROPERTY"})
 */
final class Summary implements DocumentationAttribute
{

	private string $message;

	public function __construct(string $message)
	{
		$this->message = AnnotationFilter::filterMultilineDocblock($message);
	}

	public function getType(): string
	{
		return SummaryDoc::class;
	}

	public function getArgs(): array
	{
		return [
			'message' => $this->message,
		];
	}

}
