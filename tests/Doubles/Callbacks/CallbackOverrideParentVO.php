<?php declare(strict_types = 1);

namespace Tests\Orisai\ObjectMapper\Doubles\Callbacks;

use Orisai\ObjectMapper\Callbacks\After;
use Orisai\ObjectMapper\MappedObject;
use Orisai\ObjectMapper\Rules\StringValue;

abstract class CallbackOverrideParentVO implements MappedObject
{

	/**
	 * @StringValue()
	 * @After("afterField")
	 * @After("afterFieldStatic")
	 */
	public string $field;

	public function __construct(string $field)
	{
		$this->field = $field;
	}

	protected function afterField(string $value): string
	{
		return "$value-parent";
	}

	protected static function afterFieldStatic(string $value): string
	{
		return "$value-parentStatic";
	}

}
