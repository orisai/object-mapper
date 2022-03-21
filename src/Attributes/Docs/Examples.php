<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Attributes\Docs;

use Doctrine\Common\Annotations\Annotation\NamedArgumentConstructor;
use Orisai\Exceptions\Logic\InvalidArgument;
use Orisai\ObjectMapper\Docs\ExamplesDoc;
use Orisai\ObjectMapper\Meta\DocMeta;
use function sprintf;

/**
 * @Annotation
 * @NamedArgumentConstructor()
 * @Target({"CLASS", "PROPERTY"})
 */
final class Examples implements DocumentationAttribute
{

	/** @var array<DocMeta> */
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
	 * @return array<DocMeta>
	 */
	private function resolveExamples(array $examples): array
	{
		foreach ($examples as $key => $example) {
			if (!$example instanceof Example) {
				throw InvalidArgument::create()->withMessage(sprintf(
					'%s() expects all values to be subtype of %s',
					self::class,
					Example::class,
				));
			}

			$examples[$key] = new DocMeta($example->getType(), $example->getArgs());
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
