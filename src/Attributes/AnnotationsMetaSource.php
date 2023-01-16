<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Attributes;

use Orisai\ObjectMapper\ReflectionMeta\Collector\AnnotationsCollector;

final class AnnotationsMetaSource extends BaseMetaSource
{

	public function __construct(?AnnotationsCollector $collector = null)
	{
		parent::__construct($collector ?? new AnnotationsCollector());
	}

}
