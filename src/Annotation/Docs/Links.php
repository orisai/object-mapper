<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Annotation\Docs;

use Doctrine\Common\Annotations\Annotation\Target;
use Orisai\ObjectMapper\Annotation\AnnotationMetaExtractor;
use Orisai\ObjectMapper\Annotation\AutoMappedAnnotation;
use Orisai\ObjectMapper\Annotation\BaseAnnotation;
use Orisai\ObjectMapper\Docs\LinksDoc;
use Orisai\ObjectMapper\Exception\InvalidAnnotation;
use function is_array;
use function sprintf;

/**
 * @Annotation
 * @Target({"CLASS", "PROPERTY"})
 * @property-write array<Link> $links
 */
final class Links implements DocumentationAnnotation
{

	use AutoMappedAnnotation;

	protected function getMainProperty(): string
	{
		return 'links';
	}

	/**
	 * @param array<mixed> $args
	 * @return array<mixed>
	 */
	protected function resolveArgs(array $args): array
	{
		$links = $args['links'] ?? null;

		if ($links instanceof BaseAnnotation) {
			$links = [
				$links,
			];
		}

		if (!is_array($links)) {
			throw InvalidAnnotation::create()
				->withMessage(sprintf(
					'%s() should contain array of %s',
					self::class,
					Link::class,
				));
		}

		foreach ($links as $key => $link) {
			if (!$link instanceof Link) {
				throw InvalidAnnotation::create()
					->withMessage(sprintf(
						'%s() expects all values to be subtype of %s',
						self::class,
						Link::class,
					));
			}

			$links[$key] = AnnotationMetaExtractor::extract($link);
		}

		$args['links'] = $links;

		return $args;
	}

	public function getType(): string
	{
		return LinksDoc::class;
	}

}
