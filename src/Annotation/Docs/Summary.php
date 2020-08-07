<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Annotation\Docs;

use Doctrine\Common\Annotations\Annotation\Target;
use Orisai\ObjectMapper\Annotation\AnnotationFilter;
use Orisai\ObjectMapper\Annotation\AutoMappedAnnotation;
use Orisai\ObjectMapper\Docs\SummaryDoc;
use function array_key_exists;
use function is_string;

/**
 * @Annotation
 * @Target({"CLASS", "PROPERTY"})
 * @property-write string $message
 */
final class Summary implements DocumentationAnnotation
{

	use AutoMappedAnnotation;

	protected function getMainProperty(): ?string
	{
		return 'message';
	}

	/**
	 * @param array<mixed> $args
	 * @return array<mixed>
	 */
	protected function processArgs(array $args): array
	{
		if (array_key_exists('message', $args) && is_string($args['message'])) {
			$args['message'] = AnnotationFilter::filterMultilineDocblock($args['message']);
		}

		return $args;
	}

	public function getType(): string
	{
		return SummaryDoc::class;
	}

}
