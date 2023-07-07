<?php declare(strict_types = 1);

namespace Tests\Orisai\ObjectMapper\Doubles\PhpVersionSpecific;

use Orisai\ObjectMapper\MappedObject;
use Orisai\ObjectMapper\Modifiers\DefaultValue;
use Orisai\ObjectMapper\Rules\StringValue;

final class DefaultsOverrideVO implements MappedObject
{

	#[StringValue]
	public string $propertyDefault = 'property';

	#[DefaultValue('annotation')]
	#[StringValue]
	public string $annotationDefault = 'property';

	public function __construct(
		#[StringValue]
		public string $ctorDefault = 'ctor',
		#[DefaultValue('annotationCtor')]
		#[StringValue]
		public string $annotationCtorDefault = 'ctor',
	)
	{
	}

}
