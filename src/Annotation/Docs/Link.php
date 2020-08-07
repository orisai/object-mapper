<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Annotation\Docs;

use Doctrine\Common\Annotations\Annotation\Target;
use Orisai\ObjectMapper\Annotation\AutoMappedAnnotation;
use Orisai\ObjectMapper\Docs\LinkDoc;

/**
 * @Annotation
 * @Target({"ANNOTATION"})
 * @property-write string $url
 * @property-write string|null $description
 */
final class Link implements DocumentationAnnotation
{

	use AutoMappedAnnotation;

	protected function getMainProperty(): ?string
	{
		return 'url';
	}

	public function getType(): string
	{
		return LinkDoc::class;
	}

}
