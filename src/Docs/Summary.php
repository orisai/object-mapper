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
final class Summary implements DocDefinition
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
