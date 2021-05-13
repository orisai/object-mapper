<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Annotation\Docs;

use Doctrine\Common\Annotations\Annotation\Target;
use Orisai\ObjectMapper\Annotation\AnnotationFilter;
use Orisai\ObjectMapper\Annotation\AutoMappedAnnotation;
use Orisai\ObjectMapper\Docs\ExampleDoc;
use function array_key_exists;
use function is_string;

/**
 * @Annotation
 * @Target({"ANNOTATION"})
 * @property-write string $content
 * @property-write string|null $description
 */
final class Example implements DocumentationAnnotation
{

	use AutoMappedAnnotation;

	protected function getMainProperty(): string
	{
		return 'content';
	}

	/**
	 * @param array<mixed> $args
	 * @return array<mixed>
	 */
	protected function resolveArgs(array $args): array
	{
		if (array_key_exists('content', $args) && is_string($args['content'])) {
			$args['content'] = AnnotationFilter::filterMultilineDocblock($args['content']);
		}

		if (array_key_exists('description', $args) && is_string($args['description'])) {
			$args['description'] = AnnotationFilter::filterMultilineDocblock($args['description']);
		}

		return $args;
	}

	public function getType(): string
	{
		return ExampleDoc::class;
	}

}
