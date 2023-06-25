<?php declare(strict_types = 1);

namespace Tests\Orisai\ObjectMapper\Doubles\Callbacks;

final class CallbackOverrideChildVO extends CallbackOverrideParentVO
{

	protected function afterField(string $value): string
	{
		return parent::afterField($value) . '-child';
	}

	protected static function afterFieldStatic(string $value): string
	{
		return parent::afterFieldStatic($value) . '-childStatic';
	}

}
