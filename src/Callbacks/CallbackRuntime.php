<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Callbacks;

interface CallbackRuntime
{

	public const
		WITHOUT_MAPPING = 'withMapping',
		WITH_MAPPING = 'withoutMapping',
		ALWAYS = 'always';

}
