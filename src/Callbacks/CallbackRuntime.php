<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Callbacks;

interface CallbackRuntime
{

	public const
		PROCESS_WITHOUT_MAPPING = 'processWithoutMapping',
		PROCESS = 'process',
		ALWAYS = 'always';

}
