<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Annotation\Docs;

use Doctrine\Common\Annotations\Annotation\NamedArgumentConstructor;
use Doctrine\Common\Annotations\Annotation\Target;
use Orisai\ObjectMapper\Annotation\AnnotationMetaExtractor;
use Orisai\ObjectMapper\Docs\LinksDoc;
use Orisai\ObjectMapper\Exception\InvalidAnnotation;
use function sprintf;

/**
 * @Annotation
 * @NamedArgumentConstructor()
 * @Target({"CLASS", "PROPERTY"})
 */
final class Links implements DocumentationAnnotation
{

	/** @var array<mixed> */
	private array $links;

	/**
	 * @param array<mixed> $links
	 */
	public function __construct(array $links)
	{
		$this->links = $this->resolveLinks($links);
	}

	/**
	 * @param array<mixed> $links
	 * @return array<mixed>
	 */
	private function resolveLinks(array $links): array
	{
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

		return $links;
	}

	public function getType(): string
	{
		return LinksDoc::class;
	}

	/**
	 * @return array<mixed>
	 */
	public function getArgs(): array
	{
		return [
			'links' => $this->links,
		];
	}

}
