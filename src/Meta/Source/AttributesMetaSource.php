<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Meta\Source;

use Orisai\ObjectMapper\Rules\AllOf;
use Orisai\ObjectMapper\Rules\AnyOf;
use Orisai\ReflectionMeta\Reader\AttributesMetaReader;
use Orisai\SourceMap\AttributeSource;
use Orisai\SourceMap\ReflectorSource;

final class AttributesMetaSource extends ReflectorMetaSource
{

	public function __construct(?AttributesMetaReader $reader = null)
	{
		parent::__construct($reader ?? new AttributesMetaReader());
	}

	/**
	 * @template T of ReflectorSource
	 * @param T $source
	 * @return AttributeSource<T>
	 */
	protected function wrapSource(ReflectorSource $source): AttributeSource
	{
		return new AttributeSource($source);
	}

	protected function getSourceName(): string
	{
		return 'attribute';
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
