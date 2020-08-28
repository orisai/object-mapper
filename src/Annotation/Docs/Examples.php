<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Annotation\Docs;

use Orisai\ObjectMapper\Annotation\AnnotationMetaExtractor;
use Orisai\ObjectMapper\Annotation\AutoMappedAnnotation;
use Orisai\ObjectMapper\Annotation\BaseAnnotation;
use Orisai\ObjectMapper\Docs\ExamplesDoc;
use Orisai\ObjectMapper\Exception\InvalidAnnotation;
use function is_array;
use function sprintf;

/**
 * @Annotation
 * @Target({"CLASS", "PROPERTY"})
 * @property-write array<Example> $examples
 */
final class Examples implements DocumentationAnnotation
{

	use AutoMappedAnnotation;

	protected function getMainProperty(): ?string
	{
		return 'examples';
	}

	/**
	 * @param array<mixed> $args
	 * @return array<mixed>
	 */
	protected function resolveArgs(array $args): array
	{
		$examples = $args['examples'] ?? null;

		if ($examples instanceof BaseAnnotation) {
			$examples = [
				$examples,
			];
		}

		if (!is_array($examples)) {
			throw InvalidAnnotation::create()
				->withMessage(sprintf(
					'%s() should contain array of %s',
					self::class,
					Example::class,
				));
		}

		foreach ($examples as $key => $example) {
			if (!$example instanceof Example) {
				throw InvalidAnnotation::create()->withMessage(sprintf(
					'%s() expects all values to be subtype of %s',
					self::class,
					Example::class,
				));
			}

			$examples[$key] = AnnotationMetaExtractor::extract($example);
		}

		$args['examples'] = $examples;

		return $args;
	}

	public function getType(): string
	{
		return ExamplesDoc::class;
	}

}
