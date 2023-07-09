<?php declare(strict_types = 1);

namespace Tests\Orisai\ObjectMapper\Doubles\FieldNames;

use Orisai\ObjectMapper\MappedObject;

final class FieldNamesFromTraitVO implements MappedObject
{

	use FieldNamesTrait1;
	use FieldNamesTrait2;

}
