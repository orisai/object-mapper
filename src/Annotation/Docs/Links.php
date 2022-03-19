<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Annotation\Docs;

use Doctrine\Common\Annotations\Annotation\NamedArgumentConstructor;
use Doctrine\Common\Annotations\Annotation\Target;
use Orisai\ObjectMapper\Docs\LinksDoc;
use Orisai\ObjectMapper\Exception\InvalidAnnotation;
use Orisai\ObjectMapper\Meta\DocMeta;
use function sprintf;

/**
 * @Annotation
 * @NamedArgumentConstructor()
 * @Target({"CLASS", "PROPERTY"})
 */
final class Links implements DocumentationAnnotation
{

	/** @var array<DocMeta> */
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
	 * @return array<DocMeta>
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

			$links[$key] = DocMeta::from($link->getType(), $link->getArgs());
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
