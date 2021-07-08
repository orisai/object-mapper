<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Annotation\Docs;

use Doctrine\Common\Annotations\Annotation\NamedArgumentConstructor;
use Orisai\ObjectMapper\Annotation\AnnotationMetaExtractor;
use Orisai\ObjectMapper\Docs\ExamplesDoc;
use Orisai\ObjectMapper\Exception\InvalidAnnotation;
use function sprintf;

/**
 * @Annotation
 * @NamedArgumentConstructor()
 * @Target({"CLASS", "PROPERTY"})
 */
final class Examples implements DocumentationAnnotation
{

	/** @var array<mixed> */
	private array $examples;

	/**
	 * @param array<mixed> $examples
	 */
	public function __construct(array $examples)
	{
		$this->examples = $this->resolveExamples($examples);
	}

	/**
	 * @param array<mixed> $examples
	 * @return array<mixed>
	 */
	private function resolveExamples(array $examples): array
	{
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

		return $examples;
	}

	public function getType(): string
	{
		return ExamplesDoc::class;
	}

	/**
	 * @return array<mixed>
	 */
	public function getArgs(): array
	{
		return [
			'examples' => $this->examples,
		];
	}

}
