<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Context;

use Orisai\ObjectMapper\Meta\MetaLoader;
use Orisai\ObjectMapper\Meta\MetaResolver;
use Orisai\ObjectMapper\Meta\Shared\DefaultValueMeta;

final class ArgsFieldContext extends ArgsContext
{

	private DefaultValueMeta $default;

	public function __construct(MetaLoader $metaLoader, MetaResolver $metaResolver, DefaultValueMeta $default)
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
