<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Annotation;

use Orisai\ObjectMapper\Meta\MetaSource;

final class AnnotationMetaExtractor
{

	/**
	 * @return array<mixed>
	 */
	public static function extract(BaseAnnotation $annotation): array
	{
		return [
			MetaSource::OPTION_TYPE => $annotation->getType(),
			MetaSource::OPTION_ARGS => $annotation->getArgs(),
		];
	}

}
