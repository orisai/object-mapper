<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Meta\Source;

use Orisai\ObjectMapper\Rules\AllOf;
use Orisai\ObjectMapper\Rules\AnyOf;
use Orisai\ReflectionMeta\Reader\AnnotationsMetaReader;
use Orisai\SourceMap\AnnotationSource;
use Orisai\SourceMap\ReflectorSource;

final class AnnotationsMetaSource extends ReflectorMetaSource
{

	public function __construct(?AnnotationsMetaReader $reader = null)
	{
		parent::__construct($reader ?? new AnnotationsMetaReader());
	}

	/**
	 * @template T of ReflectorSource
	 * @param T $source
	 * @return AnnotationSource<T>
	 */
	protected function wrapSource(ReflectorSource $source): AnnotationSource
	{
		return new AnnotationSource($source);
	}

	protected function getSourceName(): string
	{
		return 'annotation';
	}

	protected function getAllOfSourceKey(): string
	{
		return AllOf::class;
	}

	protected function getAnyOfSourceKey(): string
	{
		return AnyOf::class;
	}

}
