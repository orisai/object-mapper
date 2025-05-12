<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Meta\Context;

use Orisai\ObjectMapper\Meta\MetaLoader;
use Orisai\ObjectMapper\Meta\RuntimeResolver;
use Orisai\ObjectMapper\Meta\Shared\DefaultValueMeta;

final class MetaFieldContext extends MetaContext
{

	private DefaultValueMeta $default;

	public function __construct(MetaLoader $metaLoader, RuntimeResolver $metaResolver, DefaultValueMeta $default)
	{
		parent::__construct($metaLoader, $metaResolver);
		$this->default = $default;
	}

	public function hasDefaultValue(): bool
	{
		return $this->default->hasValue();
	}

	/**
	 * @return mixed
	 */
	public function getDefaultValue()
	{
		return $this->default->getValue();
	}

}
