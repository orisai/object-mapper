<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Callbacks;

interface CallbackRuntime
{

	public const PROCESSING = 'processing';
	public const INITIALIZATION = 'initialization';
	public const ALWAYS = 'always';

	public const ALL = [
		self::PROCESSING,
		self::INITIALIZATION,
		self::ALWAYS,
	];

}
